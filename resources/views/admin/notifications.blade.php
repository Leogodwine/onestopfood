@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Notifications</h2>
    <p class="text-muted mb-0 page-header-subtitle">Account creation, pending approvals, document reviews, orders, and delivery alerts (database + email; SMS when configured).</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="dashboard-card mb-0 py-3 px-3">
            <div class="text-muted small">Total stored</div>
            <div class="fs-4 fw-semibold">{{ number_format($stats['total']) }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="dashboard-card mb-0 py-3 px-3">
            <div class="text-muted small">Unread (all users)</div>
            <div class="fs-4 fw-semibold">{{ number_format($stats['unread']) }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="dashboard-card mb-0 py-3 px-3">
            <div class="text-muted small">Last 24 hours</div>
            <div class="fs-4 fw-semibold">{{ number_format($stats['last_24h']) }}</div>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-bell"></i> Activity feed</h5>
        <span class="small text-muted">Newest first</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">When</th>
                    <th scope="col">Type</th>
                    <th scope="col">Recipient</th>
                    <th scope="col">Message</th>
                    <th scope="col" class="text-center">Read</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                    @php
                        $data = $n->data ?? [];
                        $typeBase = class_basename($n->type ?? '');
                        $typeLabel = \Illuminate\Support\Str::headline(preg_replace('/Notification$/', '', $typeBase) ?: $typeBase);
                        $msg = $data['message'] ?? (is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : '—');
                        $recipient = $n->notifiable;
                    @endphp
                    <tr class="{{ $n->read_at ? '' : 'table-warning' }}">
                        <td class="text-nowrap small">
                            <span title="{{ $n->created_at?->toIso8601String() }}">{{ $n->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i a') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-dark border">{{ $typeLabel }}</span>
                            @if(!empty($data['order_id']))
                                <a href="{{ route('admin.orders.show', $data['order_id']) }}" class="small ms-1">Order #{{ $data['order_id'] }}</a>
                            @endif
                        </td>
                        <td>
                            @if($recipient)
                                <div class="fw-medium">{{ $recipient->name }}</div>
                                <div class="small text-muted">{{ $recipient->email }} · {{ $recipient->role }}</div>
                            @else
                                <span class="text-muted small">User deleted</span>
                                <div class="small text-muted">{{ class_basename($n->notifiable_type ?? '') }} #{{ $n->notifiable_id }}</div>
                            @endif
                        </td>
                        <td class="small">{{ \Illuminate\Support\Str::limit($msg, 140) }}</td>
                        <td class="text-center">
                            @if($n->read_at)
                                <i class="bi bi-check-circle-fill text-success" title="Read {{ $n->read_at->format('Y-m-d H:i') }}"></i>
                            @else
                                <i class="bi bi-circle text-warning" title="Unread"></i>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            No notifications yet. They appear here when the app sends database notifications (e.g. order placed to customer/chef).
                            @if(config('queue.default') === 'sync')
                                <div class="small mt-2">Queue driver is <code>sync</code> — notifications run immediately.</div>
                            @else
                                <div class="small mt-2">Ensure a queue worker is running so queued notifications are written to the database.</div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notifications->hasPages())
        <div class="card-footer border-top-0 pt-0">{{ $notifications->links() }}</div>
    @endif
</div>

<div class="dashboard-card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-megaphone"></i> Notification campaigns</h5>
    </div>
    <p class="mb-0 text-muted small">
        Broadcast and targeted campaigns (in-app, email, SMS) with scheduling and analytics are not implemented yet.
        This feed shows individual notifications already stored via Laravel’s notification system.
    </p>
</div>
@endsection
