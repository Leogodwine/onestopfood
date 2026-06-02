<?php

namespace App\Models;

use App\Support\UploadedDocumentUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    /** Signed-in route that serves the file from storage/app/public. */
    public function url(): ?string
    {
        return UploadedDocumentUrl::verification($this);
    }

    /** @deprecated Use url() */
    public function publicUrl(): ?string
    {
        return $this->url();
    }

    public function fileExists(): bool
    {
        if (! $this->file_path) {
            return false;
        }

        return Storage::disk('public')->exists(ltrim($this->file_path, '/'));
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
