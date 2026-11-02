<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Free',
                'slug' => 'starter-free',
                'price' => 0.00,
                'billing_period' => 'monthly',
                'duration_days' => 30,
                'listing_limit' => 3,
                'featured_limit' => 0,
                'contact_unlock_limit' => 5,
                'description' => 'Ideal for individual owners looking to list up to 3 residential or commercial properties.',
                'features' => [
                    'Post up to 3 verified properties',
                    'Standard marketplace search placement',
                    '5 Direct buyer inquiries / phone unlocks',
                    '30 days active validity',
                    'Standard email notifications'
                ],
                'badge' => 'Free Forever',
                'target_role' => 'ALL',
                'is_active' => true,
            ],
            [
                'name' => 'Pro Seller',
                'slug' => 'pro-seller',
                'price' => 1499.00,
                'billing_period' => 'quarterly',
                'duration_days' => 90,
                'listing_limit' => 15,
                'featured_limit' => 5,
                'contact_unlock_limit' => 50,
                'description' => 'Designed for active real estate consultants, brokers, and multi-property owners.',
                'features' => [
                    'Post up to 15 properties',
                    '5 Featured listings (top search ranking)',
                    '50 Direct buyer inquiries / WhatsApp unlocks',
                    'Priority buyer auto-matching & alerts',
                    'Verified Seller trusted badge',
                    '90 days listing validity',
                    'Dedicated WhatsApp & phone support'
                ],
                'badge' => 'Most Popular',
                'target_role' => 'SELLER',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise Builder',
                'slug' => 'enterprise-builder',
                'price' => 4999.00,
                'billing_period' => 'yearly',
                'duration_days' => 365,
                'listing_limit' => -1,
                'featured_limit' => 25,
                'contact_unlock_limit' => 250,
                'description' => 'Ultimate package for builders, property developers, and high-volume commercial agencies.',
                'features' => [
                    'Unlimited property listings',
                    '25 Featured spotlight badges',
                    '250 Direct buyer phone & WhatsApp connections',
                    'Instant smart matching & automated broadcast',
                    'Prime homepage banner showcase',
                    'Private seller dossier & title deed storage',
                    'Dedicated relationship manager',
                    '365 days full validity'
                ],
                'badge' => 'Best Value',
                'target_role' => 'SELLER',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $data) {
            Package::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
