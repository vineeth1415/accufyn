<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Database\Seeder;

class SixmileBeltolaPropertySeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('role', 'SELLER')->first() ?: User::first();
        $admin = User::where('role', 'ADMIN')->first() ?: User::first();

        $properties = [
            // 5 Properties in Sixmile
            [
                'title' => 'Luxury 3 BHK Skyline Residence in Sixmile',
                'description' => 'A prime 3 BHK luxury flat at Sixmile VIP road with scenic views, marble flooring, 2 dedicated car parking slots, and modern clubhouse amenities.',
                'price' => 8200000,
                'property_type' => 'Flat',
                'bhk' => 3,
                'area_sqft' => 1650,
                'city' => 'Guwahati',
                'area' => 'Sixmile',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Sixmile Commercial Office & Showroom Hub',
                'description' => 'Double-frontage corner commercial office space in prime Sixmile commercial zone, ideal for IT firm, bank, clinic, or corporate showroom.',
                'price' => 14500000,
                'property_type' => 'Commercial Space',
                'bhk' => null,
                'area_sqft' => 2400,
                'city' => 'Guwahati',
                'area' => 'Sixmile',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Serene Green 2 BHK Apartment near Sixmile Flyover',
                'description' => 'Well-ventilated 2 BHK apartment in a gated society near Sixmile junction. Features 2 balconies, 24/7 security, power backup, and park facing.',
                'price' => 5800000,
                'property_type' => 'Apartment',
                'bhk' => 2,
                'area_sqft' => 1100,
                'city' => 'Guwahati',
                'area' => 'Sixmile',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Exquisite 4 BHK Duplex Villa in Sixmile',
                'description' => 'Opulent independent 4 BHK duplex villa with private landscaped garden, home theatre room, imported modular kitchen, and automated security systems.',
                'price' => 19500000,
                'property_type' => 'Villa',
                'bhk' => 4,
                'area_sqft' => 2800,
                'city' => 'Guwahati',
                'area' => 'Sixmile',
                'possession_status' => 'Under Construction',
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Compact Modern 1 BHK Studio Flat in Sixmile',
                'description' => 'Cozy, semi-furnished 1 BHK apartment optimal for young professionals or investment rental income, within 300m walking distance from main highway.',
                'price' => 3200000,
                'property_type' => 'Flat',
                'bhk' => 1,
                'area_sqft' => 650,
                'city' => 'Guwahati',
                'area' => 'Sixmile',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
            ],

            // 5 Properties in Beltola
            [
                'title' => 'Beltola Prime 3 BHK High-Rise Vista',
                'description' => 'Elegantly crafted 3 BHK flat situated in Beltola with panoramic hill views, swimming pool, state-of-the-art gym, and high speed elevators.',
                'price' => 7600000,
                'property_type' => 'Apartment',
                'bhk' => 3,
                'area_sqft' => 1550,
                'city' => 'Guwahati',
                'area' => 'Beltola',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Independent 4 BHK Luxury Bungalow in Beltola Tiniali',
                'description' => 'Exclusive freehold residential bungalow in Beltola Tiniali. 4 large master suites, servant quarters, private terrace garden, and vastu compliant design.',
                'price' => 22000000,
                'property_type' => 'Villa',
                'bhk' => 4,
                'area_sqft' => 3100,
                'city' => 'Guwahati',
                'area' => 'Beltola',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Spacious 2 BHK Modern Flat in Beltola',
                'description' => 'Bright, contemporary 2 BHK flat near Beltola Bazaar, boasting Italian tiles, concealed wiring, piped gas connection, and CCTV monitoring.',
                'price' => 5400000,
                'property_type' => 'Flat',
                'bhk' => 2,
                'area_sqft' => 1180,
                'city' => 'Guwahati',
                'area' => 'Beltola',
                'possession_status' => 'Under Construction',
                'image' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Beltola Main Road Commercial Retail Space',
                'description' => 'High footfall ground floor commercial retail property located directly on Beltola main road. High glass frontage, 3-phase power, ideal for franchise store.',
                'price' => 12500000,
                'property_type' => 'Commercial Space',
                'bhk' => null,
                'area_sqft' => 1800,
                'city' => 'Guwahati',
                'area' => 'Beltola',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Premium 3 BHK Penthouse with Private Terrace in Beltola',
                'description' => 'Top floor 3 BHK penthouse in Beltola with private open sky deck, wooden flooring in master bedroom, jacuzzi in master bath, and 2 designated stilt car parking.',
                'price' => 11500000,
                'property_type' => 'Apartment',
                'bhk' => 3,
                'area_sqft' => 2100,
                'city' => 'Guwahati',
                'area' => 'Beltola',
                'possession_status' => 'Ready to Move',
                'image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $matchingService = app(MatchingService::class);

        foreach ($properties as $propData) {
            $image = $propData['image'];
            unset($propData['image']);

            $prop = Property::updateOrCreate(
                [
                    'title' => $propData['title'],
                    'city' => $propData['city'],
                    'area' => $propData['area'],
                ],
                array_merge($propData, [
                    'user_id' => $seller->id,
                    'created_by_user_id' => $admin->id,
                    'status' => 'PUBLISHED',
                    'views' => rand(15, 85),
                ])
            );

            PropertyImage::firstOrCreate([
                'property_id' => $prop->id,
                'image_url' => $image,
                'sort_order' => 0,
            ]);

            // Run matching against existing requirements
            $matchingService->matchProperty($prop);
        }
    }
}
