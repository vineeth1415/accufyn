<?php

namespace App\Services;

use App\Models\MatchRecord;
use App\Models\Property;
use App\Models\Requirement;
use Illuminate\Support\Collection;

class MatchingService
{
    /**
     * Calculate match score between a Requirement and a Property.
     * Returns an integer from 0 to 100.
     */
    public function calculateScore(Requirement $requirement, Property $property): int
    {
        $score = 0;

        // 1. City & Locality Check (25 pts)
        // City is a mandatory geographic constraint. Properties in different cities cannot match.
        if (!empty($requirement->city) && !empty($property->city)) {
            if (strcasecmp(trim($requirement->city), trim($property->city)) !== 0) {
                return 0; // Reject cross-city matches completely
            }

            $score += 25;

            // If locality specified and matches property area, give bonus boost
            if (!empty($requirement->locality) && !empty($property->area)) {
                $reqLoc = strtolower(trim($requirement->locality));
                $propLoc = strtolower(trim($property->area));
                if (str_contains($propLoc, $reqLoc) || str_contains($reqLoc, $propLoc)) {
                    $score += 5;
                }
            }
        }

        // 2. Property Type Check (20 pts)
        $reqType = trim($requirement->property_type ?? '');
        $propType = trim($property->property_type ?? '');

        // Major Category Classification
        $isResidentialReq = in_array($reqType, ['Flat', 'Apartment', 'Villa']);
        $isLandReq = in_array($reqType, ['Plot', 'Land']);
        $isCommercialReq = in_array($reqType, ['Commercial Space', 'Building']);

        $isResidentialProp = in_array($propType, ['Flat', 'Apartment', 'Villa']);
        $isLandProp = in_array($propType, ['Plot', 'Land']);
        $isCommercialProp = in_array($propType, ['Commercial Space', 'Building']);

        // Major category incompatibility: A buyer looking for a Flat/Apartment CANNOT be matched with Commercial Space or Freehold Plot!
        if ($isResidentialReq && !$isResidentialProp) {
            return 0;
        }
        if ($isLandReq && !$isLandProp) {
            return 0;
        }
        if ($isCommercialReq && !$isCommercialProp) {
            return 0;
        }

        if (strcasecmp($reqType, $propType) === 0) {
            $score += 20;
        } elseif (($reqType === 'Flat' && $propType === 'Apartment') || ($reqType === 'Apartment' && $propType === 'Flat')) {
            $score += 18; // Close residential match
        } elseif ($isResidentialReq && $isResidentialProp) {
            $score += 10;
        } elseif ($isLandReq && $isLandProp) {
            $score += 18;
        } elseif ($isCommercialReq && $isCommercialProp) {
            $score += 15;
        }

        // 3. BHK Check (15 pts)
        if (!is_null($requirement->bhk) && !is_null($property->bhk)) {
            $diff = abs((int)$requirement->bhk - (int)$property->bhk);
            if ($diff === 0) {
                $score += 15;
            } elseif ($diff === 1) {
                $score += 8;
            } else {
                $score += 0;
            }
        } elseif (is_null($requirement->bhk) && is_null($property->bhk)) {
            // Non-residential or plots/commercial where BHK doesn't apply
            $score += 15;
        }

        // 4. Budget Check (25 pts)
        $price = (float)$property->price;
        $min = (float)$requirement->budget_min;
        $max = (float)$requirement->budget_max;

        if ($price >= $min && $price <= $max) {
            $score += 25;
        } elseif ($min > 0 && $max > 0) {
            $tolerance10Min = $min * 0.90;
            $tolerance10Max = $max * 1.10;
            $tolerance20Min = $min * 0.80;
            $tolerance20Max = $max * 1.20;

            if ($price >= $tolerance10Min && $price <= $tolerance10Max) {
                $score += 15;
            } elseif ($price >= $tolerance20Min && $price <= $tolerance20Max) {
                $score += 8;
            } else {
                // If price exceeds max budget by > 30%, reject as unaffordable
                if ($price > ($max * 1.30)) {
                    return 0;
                }
            }
        }

        // 5. Possession Status Check (15 pts)
        if (empty($requirement->possession_status) || strcasecmp($requirement->possession_status, 'Any') === 0) {
            $score += 15;
        } elseif (!empty($property->possession_status)) {
            if (strcasecmp(trim($requirement->possession_status), trim($property->possession_status)) === 0) {
                $score += 15;
            }
        }

        return min(100, max(0, $score));
    }

    /**
     * Match a single requirement against all published properties.
     */
    public function matchRequirement(Requirement $requirement): Collection
    {
        if ($requirement->status !== 'ACTIVE') {
            return collect();
        }

        $query = Property::where('status', 'PUBLISHED');
        if (!empty($requirement->city)) {
            $query->where('city', $requirement->city);
        }
        $properties = $query->get();
        $matchedIds = [];
        $matches = collect();

        foreach ($properties as $property) {
            $score = $this->calculateScore($requirement, $property);

            if ($score >= 45) {
                $match = MatchRecord::updateOrCreate(
                    [
                        'requirement_id' => $requirement->id,
                        'property_id' => $property->id,
                    ],
                    [
                        'score' => $score,
                    ]
                );
                $matches->push($match);
                $matchedIds[] = $property->id;
            }
        }

        // Delete any existing matches that no longer qualify (e.g. different city or score < 45)
        MatchRecord::where('requirement_id', $requirement->id)
            ->whereNotIn('property_id', $matchedIds)
            ->delete();

        return $matches->sortByDesc('score');
    }

    /**
     * Match a single property against all active requirements.
     */
    public function matchProperty(Property $property): Collection
    {
        if ($property->status !== 'PUBLISHED') {
            // If property is no longer published, delete existing matches
            MatchRecord::where('property_id', $property->id)->delete();
            return collect();
        }

        $query = Requirement::where('status', 'ACTIVE');
        if (!empty($property->city)) {
            $query->where('city', $property->city);
        }
        $requirements = $query->get();
        $matches = collect();

        foreach ($requirements as $requirement) {
            $score = $this->calculateScore($requirement, $property);

            if ($score >= 45) {
                $match = MatchRecord::updateOrCreate(
                    [
                        'requirement_id' => $requirement->id,
                        'property_id' => $property->id,
                    ],
                    [
                        'score' => $score,
                    ]
                );
                $matches->push($match);
            } else {
                MatchRecord::where('requirement_id', $requirement->id)
                    ->where('property_id', $property->id)
                    ->delete();
            }
        }

        return $matches->sortByDesc('score');
    }

    /**
     * Recalculate matches across all active requirements and published properties.
     */
    public function recalculateAll(): int
    {
        MatchRecord::truncate();
        $requirements = Requirement::where('status', 'ACTIVE')->get();
        $count = 0;

        foreach ($requirements as $requirement) {
            $matches = $this->matchRequirement($requirement);
            $count += $matches->count();
        }

        return $count;
    }
}
