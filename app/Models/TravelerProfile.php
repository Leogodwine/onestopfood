<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelerProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_registration_no',
        'is_online',
        'last_latitude',
        'last_longitude',
        'last_location_at',
        'dob',
        'nida_id',
        'driving_license_no',
        'nationality',
        'gender',
        'selfie_path',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'street_address',
        'city_district',
        'ward_neighborhood',
        'address_type',
        'proof_of_address_path',
        'license_number',
        'license_issue_date',
        'license_expiry_date',
        'license_class',
        'accident_violation_history',
        'clean_driving_record_declaration',
        'vehicle_make',
        'vehicle_model',
        'vehicle_reg_no',
        'vehicle_color',
        'vehicle_photo_path',
        'vehicle_proof_of_ownership_path',
        'vehicle_insurance_path',
        'vehicle_insurance_expiry',
        'bank_name',
        'account_holder_name',
        'account_number',
        'mobile_money_number',
        'delivery_radius',
        'preferred_zones',
        'max_load_capacity',
        'availability_schedule',
        'weekend_availability',
        'background_check_consent',
        'tos_agreement',
        'code_of_conduct_agreement',
        'criminal_record_declaration',
        'driving_violation_declaration',
        'bio',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_latitude' => 'float',
        'last_longitude' => 'float',
        'last_location_at' => 'datetime',
        'dob' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'vehicle_insurance_expiry' => 'date',
        'preferred_zones' => 'array',
        'availability_schedule' => 'array',
        'clean_driving_record_declaration' => 'boolean',
        'weekend_availability' => 'boolean',
        'background_check_consent' => 'boolean',
        'tos_agreement' => 'boolean',
        'code_of_conduct_agreement' => 'boolean',
        'criminal_record_declaration' => 'boolean',
        'driving_violation_declaration' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

