<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChefProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bio',
        'heritage_story',
        'specialties',
        'specialties_list',
        'kitchen_address',
        'kitchen_latitude',
        'kitchen_longitude',
        'food_handler_certificate_no',
        'years_experience',
        'cuisine_type',
        'dob',
        'nida_id',
        'passport_no',
        'nationality',
        'gender',
        'selfie_path',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'street_address',
        'city_district',
        'ward_neighborhood',
        'landmark_directions',
        'address_type',
        'proof_of_address_path',
        'kitchen_photos',
        'professional_training_path',
        'food_safety_cert_path',
        'prev_employment',
        'gap_explanation',
        'menu_samples',
        'business_license_path',
        'food_handling_permit_path',
        'tin_number',
        'health_inspection_cert_path',
        'bank_name',
        'account_holder_name',
        'account_number',
        'mobile_money_number',
        'background_check_consent',
        'tos_agreement',
        'code_of_conduct_agreement',
        'criminal_record_declaration',
        'operating_hours',
        'estimated_prep_time',
    ];

    protected $casts = [
        'specialties_list' => 'array',
        'kitchen_latitude' => 'float',
        'kitchen_longitude' => 'float',
        'dob' => 'date',
        'kitchen_photos' => 'array',
        'menu_samples' => 'array',
        'operating_hours' => 'array',
        'background_check_consent' => 'boolean',
        'tos_agreement' => 'boolean',
        'code_of_conduct_agreement' => 'boolean',
        'criminal_record_declaration' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

