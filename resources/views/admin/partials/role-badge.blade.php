@php
    $title = $user->effectiveAdminTitle() ?? $user->admin_title;
    $access = app(\App\Services\AdminAccessService::class);
@endphp
@if($title)
    <span class="badge bg-{{ $access->titleBadge($title) }}">{{ $access->titleLabel($title) }}</span>
@else
    <span class="badge bg-secondary">Admin</span>
@endif
