<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerificationDocument extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'document_no',
        'file_path',
        'status',
        'admin_notes',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
