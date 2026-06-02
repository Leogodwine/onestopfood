<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'label' => 'Site Name',
                'value' => 'One Stop',
                'type' => 'string',
                'group' => 'General',
                'description' => 'The name of the platform',
            ],
            [
                'key' => 'support_email',
                'label' => 'Support Email',
                'value' => 'support@onestopfood.co.tz',
                'type' => 'string',
                'group' => 'General',
                'description' => 'Contact email for support',
            ],
            [
                'key' => 'support_phone',
                'label' => 'Support Phone',
                'value' => '+255 651 490 677',
                'type' => 'string',
                'group' => 'General',
                'description' => 'Contact phone for support',
            ],
            [
                'key' => 'currency',
                'label' => 'Currency code',
                'value' => 'TZS',
                'type' => 'string',
                'group' => 'Finance',
                'description' => 'Default currency code (e.g. TZS)',
            ],
            [
                'key' => 'chef_commission_rate',
                'label' => 'Chef Commission Rate (%)',
                'value' => '10',
                'type' => 'integer',
                'group' => 'Finance',
                'description' => 'Percentage of commission taken from chef earnings',
            ],
            [
                'key' => 'traveler_commission_rate',
                'label' => 'Traveler Commission Rate (%)',
                'value' => '5',
                'type' => 'integer',
                'group' => 'Finance',
                'description' => 'Percentage of commission taken from traveler earnings',
            ],
            [
                'key' => 'base_delivery_fee',
                'label' => 'Base Delivery Fee',
                'value' => '2000',
                'type' => 'integer',
                'group' => 'Logistics',
                'description' => 'Base fee charged for any delivery',
            ],
            [
                'key' => 'delivery_fee_per_km',
                'label' => 'Delivery Fee per KM',
                'value' => '500',
                'type' => 'integer',
                'group' => 'Logistics',
                'description' => 'Additional fee per kilometer for delivery',
            ],
            [
                'key' => 'max_delivery_radius_km',
                'label' => 'Max Delivery Radius (KM)',
                'value' => '15',
                'type' => 'integer',
                'group' => 'Logistics',
                'description' => 'Maximum allowed distance for a delivery',
            ],
            [
                'key' => 'auto_assign_traveler',
                'label' => 'Auto-assign Travelers',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'Logistics',
                'description' => 'Automatically assign the nearest traveler to new orders',
            ],
            [
                'key' => 'chef_verification_required',
                'label' => 'Chef Verification Required',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'Security',
                'description' => 'Require manual approval for new chefs',
            ],
            [
                'key' => 'traveler_verification_required',
                'label' => 'Traveler Verification Required',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'Security',
                'description' => 'Require manual approval for new travelers',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
