<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'created_by_user_id',
        'category',
        'description',
        'status',
        'penalty_amount',
        'compensation_amount',
        'resolution_notes',
        'resolved_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'penalty_amount' => 'decimal:2',
            'compensation_amount' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_admin_id');
    }
}

