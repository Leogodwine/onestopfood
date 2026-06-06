@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Notifications</h2>
        @if(auth()->user()->unreadNotifications->isNotEmpty())
        <div class="page-header-actions">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary page-header-action-btn">
                    Mark all read
                </button>
            </form>
        </div>
        @endif
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Account updates, verification progress, and order alerts.</p>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="dashboard-card">
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $message = $data['message'] ?? 'Notification';
                $url = $data['url'] ?? null;
            @endphp
            <div class="list-group-item {{ $notification->read_at ? '' : 'list-group-item-warning' }}">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold">{{ $message }}</div>
                        <div class="small text-muted">
                            {{ $notification->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i a') }}
                        </div>
                    </div>
                    @if(! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                {{ $url ? 'Open' : 'Mark read' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">No notifications yet.</div>
        @endforelse
    </div>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
