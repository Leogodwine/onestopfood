<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'is_admin',
        'successful',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'reason',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'is_admin' => 'boolean',
            'logged_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

