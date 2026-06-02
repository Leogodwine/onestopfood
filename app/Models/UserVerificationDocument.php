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

    public function publicUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    public function isImage(): bool
    {
        if (! $this->file_path) {
            return false;
        }

        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }
}
