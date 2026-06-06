<?php

namespace App\Notifications;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserVerificationDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $event,
        public array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brand = SystemSetting::getValue('site_name', config('app.name', 'One Stop Food'));
        $copy = $this->copy();

        return (new MailMessage)
            ->subject($copy['subject'].' – '.$brand)
            ->view('emails.account-event', [
                'brand' => $brand,
                'headline' => $copy['headline'],
                'body' => $copy['body'],
                'actionUrl' => $copy['action_url'] ?? route('dashboard'),
                'actionLabel' => $copy['action_label'] ?? 'Open dashboard',
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $copy = $this->copy();

        return [
            'type' => $this->event,
            'event' => $this->event,
            'category' => $this->category(),
            'message' => $copy['message'],
            'body' => $copy['body'],
            'sms_text' => $this->context['sms_text'] ?? null,
            'user_id' => $this->context['user_id'] ?? null,
            'document_id' => $this->context['document_id'] ?? null,
            'document_type' => $this->context['document_type'] ?? null,
            'url' => $copy['action_url'] ?? null,
            'channels_sent' => $this->context['channels_sent'] ?? $this->defaultChannels($notifiable),
        ];
    }

    /** @return array<int, string> */
    private function defaultChannels(object $notifiable): array
    {
        $channels = ['in_app'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'email';
        }

        return $channels;
    }

    private function category(): string
    {
        if (str_starts_with($this->event, 'admin_')) {
            return 'admin';
        }

        if (str_starts_with($this->event, 'deletion_') || str_starts_with($this->event, 'account_') || str_starts_with($this->event, 'document_') || in_array($this->event, ['verification_submitted', 'pending_approval', 'all_documents_verified'], true)) {
            return 'account';
        }

        return 'general';
    }

    /** @return array{subject: string, headline: string, body: string, message: string, action_url?: string, action_label?: string} */
    private function copy(): array
    {
        $userName = (string) ($this->context['user_name'] ?? 'Partner');
        $docLabel = (string) ($this->context['document_label'] ?? 'document');

        return match ($this->event) {
            'account_created' => [
                'subject' => 'Welcome to OneStopFood',
                'headline' => 'Account created successfully',
                'body' => "Hello {$userName}, your OneStopFood account has been created. Complete your verification profile so our team can review your application.",
                'message' => 'Welcome! Your OneStopFood account was created successfully.',
                'action_url' => route('verification.status'),
                'action_label' => 'Track verification',
            ],
            'verification_submitted' => [
                'subject' => 'Verification submitted',
                'headline' => 'Application received',
                'body' => "Hello {$userName}, we received your verification details. Our team will review your documents and notify you at each step.",
                'message' => 'Your verification application was submitted and is pending review.',
                'action_url' => route('verification.status'),
                'action_label' => 'Track approval steps',
            ],
            'pending_approval' => [
                'subject' => 'Application pending approval',
                'headline' => 'Pending admin review',
                'body' => "Hello {$userName}, your OneStopFood partner application is pending approval. You will receive email and SMS updates when the status changes.",
                'message' => 'Your account is pending approval.',
                'action_url' => route('verification.status'),
                'action_label' => 'View status',
            ],
            'account_approved' => [
                'subject' => 'Account approved',
                'headline' => 'Congratulations — you are approved!',
                'body' => "Hello {$userName}, your OneStopFood partner account has been approved. You can now access your full dashboard.",
                'message' => 'Your OneStopFood account has been approved.',
                'action_url' => route('dashboard'),
                'action_label' => 'Go to dashboard',
            ],
            'account_rejected' => [
                'subject' => 'Application update',
                'headline' => 'Application not approved',
                'body' => 'Hello '.$userName.', your partner application was not approved.'.(filled($this->context['reason'] ?? null) ? ' Reason: '.$this->context['reason'] : ''),
                'message' => 'Your partner application was not approved.',
                'action_url' => route('verification.status'),
                'action_label' => 'View details',
            ],
            'document_approved' => [
                'subject' => 'Document approved',
                'headline' => "{$docLabel} approved",
                'body' => "Hello {$userName}, your {$docLabel} was verified and approved on OneStopFood.",
                'message' => "Your {$docLabel} was approved.",
                'action_url' => route('verification.status'),
                'action_label' => 'Track progress',
            ],
            'document_rejected' => [
                'subject' => 'Document needs attention',
                'headline' => "{$docLabel} rejected",
                'body' => "Hello {$userName}, your {$docLabel} needs to be updated.". (filled($this->context['admin_notes'] ?? null) ? ' Note: '.$this->context['admin_notes'] : ''),
                'message' => "Your {$docLabel} was rejected.",
                'action_url' => route('verification.show'),
                'action_label' => 'Update documents',
            ],
            'all_documents_verified' => [
                'subject' => 'All documents verified',
                'headline' => 'Documents verified',
                'body' => "Hello {$userName}, all your verification documents have been approved. Final account approval will follow shortly if not already complete.",
                'message' => 'All your verification documents are approved.',
                'action_url' => route('verification.status'),
                'action_label' => 'View status',
            ],
            'admin_new_account' => [
                'subject' => 'New partner account',
                'headline' => 'New account pending review',
                'body' => "A new {$this->context['role_label']} account ({$userName}) was created and may need review.",
                'message' => "New {$this->context['role_label']} account: {$userName}.",
                'action_url' => $this->context['admin_url'] ?? route('admin.users.index'),
                'action_label' => 'Review users',
            ],
            'admin_verification_submitted' => [
                'subject' => 'Verification submitted',
                'headline' => 'Partner submitted verification',
                'body' => "{$userName} submitted verification documents for review.",
                'message' => "{$userName} submitted verification for review.",
                'action_url' => $this->context['admin_url'] ?? route('admin.verifications.index'),
                'action_label' => 'Review documents',
            ],
            'account_self_deactivated' => [
                'subject' => 'Account deactivated',
                'headline' => 'Your account is deactivated',
                'body' => "Hello {$userName}, your account has been deactivated. You can sign in at any time and reactivate from Account Settings.",
                'message' => 'Your account was deactivated. You can reactivate anytime from Account Settings.',
                'action_url' => route('account.settings'),
                'action_label' => 'Account settings',
            ],
            'account_self_reactivated' => [
                'subject' => 'Account reactivated',
                'headline' => 'Welcome back!',
                'body' => "Hello {$userName}, your account is active again. You can use OneStopFood as normal.",
                'message' => 'Your account has been reactivated.',
                'action_url' => route('dashboard'),
                'action_label' => 'Go to dashboard',
            ],
            'deletion_requested_user' => [
                'subject' => 'Deletion request received',
                'headline' => 'Permanent deletion requested',
                'body' => "Hello {$userName}, we received your request to permanently delete your account. An administrator will review your reason and confirm the action.",
                'message' => 'Your permanent account deletion request is pending admin review.',
                'action_url' => route('account.settings'),
                'action_label' => 'View request status',
            ],
            'admin_deletion_requested' => [
                'subject' => 'Account deletion request',
                'headline' => 'User requested permanent deletion',
                'body' => "{$userName} requested permanent account deletion.".(! empty($this->context['reason']) ? ' Reason: '.$this->context['reason'] : ''),
                'message' => "{$userName} requested permanent account deletion.",
                'action_url' => $this->context['admin_url'] ?? route('admin.users.index'),
                'action_label' => 'Review request',
            ],
            'deletion_approved' => [
                'subject' => 'Account deleted',
                'headline' => 'Account permanently removed',
                'body' => "Hello {$userName}, your account deletion request was approved and your account has been permanently removed from OneStopFood.",
                'message' => 'Your account was permanently deleted as requested.',
                'action_url' => home_url(),
                'action_label' => 'Visit homepage',
            ],
            'deletion_rejected' => [
                'subject' => 'Deletion request update',
                'headline' => 'Deletion request not approved',
                'body' => 'Hello '.$userName.', your permanent deletion request was not approved.'.(! empty($this->context['admin_notes']) ? ' Note: '.$this->context['admin_notes'] : ''),
                'message' => 'Your account deletion request was not approved.',
                'action_url' => route('account.settings'),
                'action_label' => 'Account settings',
            ],
            default => [
                'subject' => 'Account update',
                'headline' => 'OneStopFood account update',
                'body' => 'There is an update on your OneStopFood account.',
                'message' => 'Account update from OneStopFood.',
                'action_url' => route('dashboard'),
                'action_label' => 'Open dashboard',
            ],
        };
    }

    public static function contextForUser(User $user): array
    {
        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
        ];
    }

    public static function contextForDocument(UserVerificationDocument $document): array
    {
        $user = $document->user;

        return [
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'document_id' => $document->id,
            'document_type' => $document->type,
            'document_label' => \Illuminate\Support\Str::headline(str_replace('_', ' ', $document->type)),
            'admin_notes' => $document->admin_notes,
        ];
    }
}
