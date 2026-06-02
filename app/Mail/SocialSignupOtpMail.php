<?php

namespace App\Mail;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SocialSignupOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your verification code – '.SystemSetting::getValue('site_name', config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.social-signup-otp',
        );
    }
}
