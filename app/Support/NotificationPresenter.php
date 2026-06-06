<?php

namespace App\Support;

use Illuminate\Notifications\DatabaseNotification;

class NotificationPresenter
{
    /** @return array{message: string, body: ?string, url: ?string, category: string, category_label: string, channels: array<int, string>, icon: string, type: ?string} */
    public static function present(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];
        $type = (string) ($data['type'] ?? $data['event'] ?? '');
        $category = (string) ($data['category'] ?? self::categoryFromType($type));

        return [
            'message' => (string) ($data['message'] ?? __('notifications.generic')),
            'body' => filled($data['body'] ?? null) ? (string) $data['body'] : null,
            'sms_text' => filled($data['sms_text'] ?? null) ? (string) $data['sms_text'] : null,
            'url' => filled($data['url'] ?? null) ? (string) $data['url'] : null,
            'category' => $category,
            'category_label' => __('notifications.categories.'.$category),
            'channels' => array_values(array_unique($data['channels_sent'] ?? ['in_app'])),
            'icon' => self::iconForCategory($category),
            'type' => $type !== '' ? $type : null,
        ];
    }

    public static function categoryFromType(string $type): string
    {
        if (str_starts_with($type, 'order_') || in_array($type, ['delivery_assigned', 'order_placed', 'order_portion_placed'], true)) {
            return 'orders';
        }

        if (str_starts_with($type, 'payment_')) {
            return 'payments';
        }

        if (str_starts_with($type, 'admin_')) {
            return 'admin';
        }

        if (in_array($type, [
            'account_created', 'verification_submitted', 'pending_approval', 'account_approved',
            'account_rejected', 'document_approved', 'document_rejected', 'all_documents_verified',
            'account_self_deactivated', 'account_self_reactivated',
            'deletion_requested_user', 'deletion_approved', 'deletion_rejected',
        ], true)) {
            return 'account';
        }

        return 'general';
    }

    public static function iconForCategory(string $category): string
    {
        return match ($category) {
            'orders' => 'bi-bag-check',
            'payments' => 'bi-credit-card',
            'account' => 'bi-person-badge',
            'admin' => 'bi-shield-check',
            default => 'bi-bell',
        };
    }
}
