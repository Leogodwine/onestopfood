<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type',
        'base_fee',
        'traveler_capacity',
        'polygon',
        'operating_hours',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'polygon' => 'array',
            'operating_hours' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}

