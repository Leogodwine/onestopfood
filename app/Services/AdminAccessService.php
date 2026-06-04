<?php

namespace App\Services;

use App\Models\User;

class AdminAccessService
{
    public function effectiveTitle(User $user): ?string
    {
        if ($user->role !== User::ROLE_ADMIN) {
            return null;
        }

        if ($user->admin_title && $this->isValidTitle($user->admin_title)) {
            return $user->admin_title;
        }

        if ($user->is_super_admin) {
            return User::ADMIN_TITLE_SYSTEM_ADMINISTRATOR;
        }

        return User::ADMIN_TITLE_MANAGER;
    }

    public function can(User $user, string $permission): bool
    {
        if ($user->role !== User::ROLE_ADMIN) {
            return false;
        }

        $title = $this->effectiveTitle($user);
        $permissions = config('admin_access.permissions', []);
        $allowed = $permissions[$permission] ?? [];

        return in_array($title, $allowed, true);
    }

    /**
     * @return array<string, bool>
     */
    public function permissionsMap(User $user): array
    {
        $map = [];

        foreach (array_keys(config('admin_access.permissions', [])) as $permission) {
            $map[$permission] = $this->can($user, $permission);
        }

        return $map;
    }

    public function titleLabel(?string $title): string
    {
        if (! $title) {
            return 'Admin';
        }

        return (string) config("admin_access.titles.{$title}.label", ucfirst(str_replace('_', ' ', $title)));
    }

    public function titleShort(?string $title): string
    {
        if (! $title) {
            return 'Admin';
        }

        return (string) config("admin_access.titles.{$title}.short", $this->titleLabel($title));
    }

    public function titleDescription(?string $title): string
    {
        if (! $title) {
            return '';
        }

        return (string) config("admin_access.titles.{$title}.description", '');
    }

    public function titleBadge(?string $title): string
    {
        if (! $title) {
            return 'secondary';
        }

        return (string) config("admin_access.titles.{$title}.badge", 'secondary');
    }

    /**
     * @return array<string, array{label: string, short: string, description: string, badge: string}>
     */
    public function titles(): array
    {
        return config('admin_access.titles', []);
    }

    public function isValidTitle(string $title): bool
    {
        return array_key_exists($title, config('admin_access.titles', []));
    }
}
