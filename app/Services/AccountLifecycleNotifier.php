<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerificationDocument;
use App\Notifications\AccountEventNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountLifecycleNotifier
{
    public function __construct(
        private readonly SmsService $sms,
    ) {}

    public function accountCreated(User $user): void
    {
        $context = AccountEventNotification::contextForUser($user);
        $user->notify(new AccountEventNotification('account_created', $context));
        $this->sms->send($user->phone, $this->smsText('account_created', $user));

        if (in_array($user->role, [User::ROLE_CHEF, User::ROLE_TRAVELER], true)) {
            $user->notify(new AccountEventNotification('pending_approval', $context));
            $this->sms->send($user->phone, $this->smsText('pending_approval', $user));
        }

        $this->notifyAdmins('admin_new_account', $user, [
            'role_label' => $this->roleLabel($user->role),
            'admin_url' => route('admin.users.show', $user),
        ]);
    }

    public function verificationSubmitted(User $user): void
    {
        $context = AccountEventNotification::contextForUser($user);
        $user->notify(new AccountEventNotification('verification_submitted', $context));
        $user->notify(new AccountEventNotification('pending_approval', $context));
        $this->sms->send($user->phone, $this->smsText('verification_submitted', $user));

        $this->notifyAdmins('admin_verification_submitted', $user, [
            'admin_url' => route('admin.users.show', $user),
        ]);
    }

    public function accountApproved(User $user): void
    {
        $context = AccountEventNotification::contextForUser($user);
        $user->notify(new AccountEventNotification('account_approved', $context));
        $this->sms->send($user->phone, $this->smsText('account_approved', $user));
    }

    public function accountRejected(User $user, ?string $reason = null): void
    {
        $context = array_merge(AccountEventNotification::contextForUser($user), [
            'reason' => $reason,
        ]);
        $user->notify(new AccountEventNotification('account_rejected', $context));
        $this->sms->send($user->phone, $this->smsText('account_rejected', $user));
    }

    public function documentReviewed(UserVerificationDocument $document, string $status): void
    {
        $user = $document->user;
        if (! $user) {
            return;
        }

        $context = AccountEventNotification::contextForDocument($document);
        $event = $status === 'approved' ? 'document_approved' : 'document_rejected';
        $user->notify(new AccountEventNotification($event, $context));
        $this->sms->send($user->phone, $this->smsText($event, $user, $context));

        if ($status === 'approved') {
            $pending = $user->verificationDocuments()->where('status', 'pending')->count();
            $rejected = $user->verificationDocuments()->where('status', 'rejected')->count();
            if ($pending === 0 && $rejected === 0) {
                $allContext = AccountEventNotification::contextForUser($user);
                $user->notify(new AccountEventNotification('all_documents_verified', $allContext));
                $this->sms->send($user->phone, $this->smsText('all_documents_verified', $user));
            }
        }
    }

    public function accountApprovedAfterDocuments(User $user): void
    {
        $this->accountApproved($user);
    }

    public function accountSelfDeactivated(User $user): void
    {
        $context = AccountEventNotification::contextForUser($user);
        $user->notify(new AccountEventNotification('account_self_deactivated', $context));
        $this->sms->send($user->phone, $this->smsText('account_self_deactivated', $user));
    }

    public function accountSelfReactivated(User $user): void
    {
        $context = AccountEventNotification::contextForUser($user);
        $user->notify(new AccountEventNotification('account_self_reactivated', $context));
        $this->sms->send($user->phone, $this->smsText('account_self_reactivated', $user));
    }

    public function deletionRequested(User $user, \App\Models\AccountActionRequest $request): void
    {
        $context = array_merge(AccountEventNotification::contextForUser($user), [
            'reason' => $request->reason,
            'request_id' => $request->id,
            'admin_url' => route('admin.users.show', $user).'#account-requests',
        ]);

        $user->notify(new AccountEventNotification('deletion_requested_user', $context));
        $this->sms->send($user->phone, $this->smsText('deletion_requested_user', $user));

        $this->notifyAdmins('admin_deletion_requested', $user, $context);
        $this->smsAdminsAlert($this->smsText('admin_deletion_requested', $user, $context));
    }

    public function deletionApproved(User $user, \App\Models\AccountActionRequest $request, ?string $adminNotes = null): void
    {
        $context = array_merge(AccountEventNotification::contextForUser($user), [
            'admin_notes' => $adminNotes,
            'request_id' => $request->id,
        ]);
        $user->notify(new AccountEventNotification('deletion_approved', $context));
        $this->sms->send($user->phone, $this->smsText('deletion_approved', $user));
    }

    public function deletionRejected(User $user, \App\Models\AccountActionRequest $request, ?string $adminNotes = null): void
    {
        $context = array_merge(AccountEventNotification::contextForUser($user), [
            'admin_notes' => $adminNotes,
            'request_id' => $request->id,
        ]);
        $user->notify(new AccountEventNotification('deletion_rejected', $context));
        $this->sms->send($user->phone, $this->smsText('deletion_rejected', $user, $context));
    }

    private function notifyAdmins(string $event, User $subject, array $extra = []): void
    {
        $context = array_merge(AccountEventNotification::contextForUser($subject), $extra);

        User::query()
            ->where('role', User::ROLE_ADMIN)
            ->get()
            ->each(function (User $admin) use ($event, $context) {
                try {
                    if ($admin->adminCan('users.view') || $admin->adminCan('verifications')) {
                        $admin->notify(new AccountEventNotification($event, $context));
                    }
                } catch (\Throwable $e) {
                    Log::warning('Admin notification failed', ['admin_id' => $admin->id, 'error' => $e->getMessage()]);
                }
            });
    }

    private function smsAdminsAlert(string $message): void
    {
        $phones = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->whereNotNull('phone')
            ->pluck('phone');

        foreach ($phones as $phone) {
            $this->sms->send($phone, $message);
        }

        $supportPhone = config('contacts.support_phone');
        if ($supportPhone) {
            $this->sms->send($supportPhone, $message);
        }
    }

  /** @param array<string, mixed> $context */
    private function smsText(string $event, User $user, array $context = []): string
    {
        $brand = 'OneStopFood';
        $name = Str::before($user->name, ' ') ?: $user->name;
        $doc = (string) ($context['document_label'] ?? 'document');

        return match ($event) {
            'account_created' => "{$brand}: Welcome {$name}! Your account was created. Complete verification at onestopfood.co.tz",
            'pending_approval', 'verification_submitted' => "{$brand}: Hi {$name}, your application is pending review. Track status in your dashboard.",
            'account_approved', 'all_documents_verified' => "{$brand}: Hi {$name}, your account/documents are approved. You can now use OneStopFood fully.",
            'account_rejected' => "{$brand}: Hi {$name}, your application needs attention. Please check your email or dashboard.",
            'document_approved' => "{$brand}: Hi {$name}, your {$doc} was approved.",
            'document_rejected' => "{$brand}: Hi {$name}, your {$doc} needs an update. Please check your dashboard.",
            'account_self_deactivated' => "{$brand}: Hi {$name}, your account was deactivated. Sign in anytime to reactivate from Account Settings.",
            'account_self_reactivated' => "{$brand}: Hi {$name}, your account is active again. Welcome back to OneStopFood.",
            'deletion_requested_user' => "{$brand}: Hi {$name}, we received your account deletion request. An admin will review it shortly.",
            'admin_deletion_requested' => "{$brand} ADMIN: {$name} ({$user->role}) requested permanent account deletion. Review in admin panel.",
            'deletion_approved' => "{$brand}: Hi {$name}, your account deletion request was approved and your account has been removed.",
            'deletion_rejected' => "{$brand}: Hi {$name}, your account deletion request was not approved.".(! empty($context['admin_notes']) ? ' Note: '.$context['admin_notes'] : ''),
            default => "{$brand}: You have an account update. Please sign in to OneStopFood.",
        };
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            User::ROLE_CHEF => 'chef',
            User::ROLE_TRAVELER => 'traveler',
            User::ROLE_ADMIN => 'admin',
            default => 'customer',
        };
    }
}
