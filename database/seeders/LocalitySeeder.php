<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Locality;
use Illuminate\Database\Seeder;

class LocalitySeeder extends Seeder
{
    public function run(): void
    {
        $cityLocalities = [
            'Guwahati' => [
                'Sixmile',
                'Beltola',
                'Zoo Road',
                'GS Road',
                'Christian Basti',
                'Dispur',
                'Pan Bazar',
                'Uzan Bazar',
                'Jalukbari',
                'Chandmari',
                'Kahilipara',
                'Lalganesh',
                'Bhangagarh',
                'Hatigaon',
            ],
            'Hyderabad' => [
                'Hitec City',
                'Madhapur',
                'Gachibowli',
                'Banjara Hills',
                'Jubilee Hills',
                'Kondapur',
                'Kukatpally',
                'Manikonda',
                'Financial District',
            ],
            'Bangalore' => [
                'Indiranagar',
                'Whitefield',
                'Koramangala',
                'HSR Layout',
                'Electronic City',
                'Marathahalli',
                'Bellandur',
                'Sarjapur Road',
                'JP Nagar',
            ],
            'Mumbai' => [
                'Bandra West',
                'Andheri West',
                'Juhu',
                'Powai',
                'Worli',
                'Malad West',
                'Thane West',
                'Lower Parel',
            ],
            'Delhi NCR' => [
                'Cyber City',
                'Golf Course Road',
                'Sector 62 Noida',
                'South Extension',
                'Vasant Kunj',
                'Dwarka',
                'Indirapuram',
            ],
            'Kolkata' => [
                'Salt Lake Sector V',
                'New Town Action Area 1',
                'Park Street',
                'Ballygunge',
                'Rajarhat',
                'Alipore',
            ],
            'Pune' => [
                'Hinjewadi Phase 1',
                'Baner',
                'Wakad',
                'Viman Nagar',
                'Kharadi',
                'Kothrud',
            ],
        ];

        foreach ($cityLocalities as $cityName => $localities) {
            $city = City::where('name', $cityName)->first();
            $cityId = $city ? $city->id : null;

            foreach ($localities as $localityName) {
                Locality::updateOrCreate(
                    [
                        'city' => $cityName,
                        'name' => $localityName,
                    ],
                    [
                        'city_id' => $cityId,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
