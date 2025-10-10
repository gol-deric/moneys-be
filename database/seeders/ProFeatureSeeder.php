<?php

namespace Database\Seeders;

use App\Models\ProFeature;
use Illuminate\Database\Seeder;

class ProFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'name' => 'Unlimited Subscriptions',
                'key' => 'unlimited_subscriptions',
                'description' => 'Add unlimited subscriptions without any limit (Free: 3 max)',
                'is_enabled' => true,
                'price' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Custom Notification Days',
                'key' => 'custom_notifications',
                'description' => 'Customize reminder days before payment (Free: fixed 1 day before)',
                'is_enabled' => true,
                'price' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Advanced Reports',
                'key' => 'advanced_reports',
                'description' => 'View detailed reports, charts, and spending analytics',
                'is_enabled' => true,
                'price' => 0,
                'sort_order' => 3,
            ],
            [
                'name' => 'Export Data',
                'key' => 'export_data',
                'description' => 'Export your data to PDF and CSV formats',
                'is_enabled' => true,
                'price' => 0,
                'sort_order' => 4,
            ],
            [
                'name' => 'Unlimited History',
                'key' => 'unlimited_history',
                'description' => 'Keep your subscription history forever (Free: 30 days only)',
                'is_enabled' => true,
                'price' => 0,
                'sort_order' => 5,
            ],
        ];

        foreach ($features as $feature) {
            ProFeature::updateOrCreate(
                ['key' => $feature['key']],
                $feature
            );
        }
    }
}
