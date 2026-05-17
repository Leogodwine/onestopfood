<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->date('dob')->nullable();
            $table->string('nida_id')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('street_address')->nullable();
            $table->string('city_district')->nullable();
            $table->string('ward_neighborhood')->nullable();
            $table->string('landmark_directions')->nullable();
            $table->string('address_type')->nullable(); // Home, Commercial, Shared, Restaurant
            $table->string('proof_of_address_path')->nullable();
            $table->json('kitchen_photos')->nullable();
            $table->string('professional_training_path')->nullable();
            $table->string('food_safety_cert_path')->nullable();
            $table->text('prev_employment')->nullable();
            $table->text('gap_explanation')->nullable();
            $table->json('menu_samples')->nullable();
            $table->string('business_license_path')->nullable();
            $table->string('food_handling_permit_path')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('health_inspection_cert_path')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('mobile_money_number')->nullable();
            $table->boolean('background_check_consent')->default(false);
            $table->boolean('tos_agreement')->default(false);
            $table->boolean('code_of_conduct_agreement')->default(false);
            $table->boolean('criminal_record_declaration')->default(false);
            $table->json('operating_hours')->nullable();
            $table->string('estimated_prep_time')->nullable();
        });

        Schema::table('traveler_profiles', function (Blueprint $table) {
            $table->date('dob')->nullable();
            $table->string('nida_id')->nullable();
            $table->string('driving_license_no')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('street_address')->nullable();
            $table->string('city_district')->nullable();
            $table->string('ward_neighborhood')->nullable();
            $table->string('address_type')->nullable();
            $table->string('proof_of_address_path')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_issue_date')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('license_class')->nullable();
            $table->text('accident_violation_history')->nullable();
            $table->boolean('clean_driving_record_declaration')->default(false);
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_reg_no')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('vehicle_photo_path')->nullable();
            $table->string('vehicle_proof_of_ownership_path')->nullable();
            $table->string('vehicle_insurance_path')->nullable();
            $table->date('vehicle_insurance_expiry')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('mobile_money_number')->nullable();
            $table->float('delivery_radius')->nullable();
            $table->json('preferred_zones')->nullable();
            $table->string('max_load_capacity')->nullable();
            $table->json('availability_schedule')->nullable();
            $table->boolean('weekend_availability')->default(false);
            $table->boolean('background_check_consent')->default(false);
            $table->boolean('tos_agreement')->default(false);
            $table->boolean('code_of_conduct_agreement')->default(false);
            $table->boolean('criminal_record_declaration')->default(false);
            $table->boolean('driving_violation_declaration')->default(false);
            $table->text('bio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'dob', 'nida_id', 'passport_no', 'nationality', 'gender', 'selfie_path',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'street_address', 'city_district', 'ward_neighborhood', 'landmark_directions',
                'address_type', 'proof_of_address_path', 'kitchen_photos', 'professional_training_path',
                'food_safety_cert_path', 'prev_employment', 'gap_explanation', 'menu_samples',
                'business_license_path', 'food_handling_permit_path', 'tin_number',
                'health_inspection_cert_path', 'bank_name', 'account_holder_name', 'account_number',
                'mobile_money_number', 'background_check_consent', 'tos_agreement',
                'code_of_conduct_agreement', 'criminal_record_declaration', 'operating_hours',
                'estimated_prep_time'
            ]);
        });

        Schema::table('traveler_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'dob', 'nida_id', 'driving_license_no', 'nationality', 'gender', 'selfie_path',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'street_address', 'city_district', 'ward_neighborhood', 'address_type',
                'proof_of_address_path', 'license_number', 'license_issue_date', 'license_expiry_date',
                'license_class', 'accident_violation_history', 'clean_driving_record_declaration',
                'vehicle_make', 'vehicle_model', 'vehicle_reg_no', 'vehicle_color',
                'vehicle_photo_path', 'vehicle_proof_of_ownership_path', 'vehicle_insurance_path',
                'vehicle_insurance_expiry', 'bank_name', 'account_holder_name', 'account_number',
                'mobile_money_number', 'delivery_radius', 'preferred_zones', 'max_load_capacity',
                'availability_schedule', 'weekend_availability', 'background_check_consent',
                'tos_agreement', 'code_of_conduct_agreement', 'criminal_record_declaration',
                'driving_violation_declaration', 'bio'
            ]);
        });
    }
};
