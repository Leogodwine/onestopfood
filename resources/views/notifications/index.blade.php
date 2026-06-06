@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">{{ __('dashboard.notifications') }}</h2>
        @if(auth()->user()->unreadNotifications->isNotEmpty())
        <div class="page-header-actions">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary page-header-action-btn">
                    {{ __('notifications.mark_all_read') }}
                </button>
            </form>
        </div>
        @endif
    </div>
    <p class="text-muted mb-0 page-header-subtitle">{{ __('notifications.subtitle') }}</p>
</div>

<div class="dashboard-card">
    <div class="list-group list-group-flush notification-inbox">
        @forelse($notifications as $notification)
            @php
                $item = \App\Support\NotificationPresenter::present($notification);
            @endphp
            <div class="list-group-item notification-inbox-item {{ $notification->read_at ? '' : 'notification-inbox-item--unread' }}">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="bi {{ $item['icon'] }} me-1"></i>{{ $item['category_label'] }}
                            </span>
                            @foreach($item['channels'] as $channel)
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                                    {{ __('notifications.channels.'.$channel) }}
                                </span>
                            @endforeach
                            @unless($notification->read_at)
                                <span class="badge bg-warning text-dark">{{ __('notifications.channels.in_app') }} · unread</span>
                            @endunless
                        </div>
                        <div class="fw-semibold mb-1">{{ $item['message'] }}</div>
                        @if($item['body'])
                            <p class="text-muted small mb-2 notification-inbox-body">{{ $item['body'] }}</p>
                        @endif
                        @if($item['sms_text'] && $item['sms_text'] !== $item['body'])
                            <p class="small mb-2 notification-inbox-sms">
                                <span class="fw-semibold">{{ __('notifications.sms_label') }}:</span>
                                {{ $item['sms_text'] }}
                            </p>
                        @endif
                        <div class="small text-muted">
                            {{ $notification->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i a') }}
                        </div>
                    </div>
                    @unless($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                {{ $item['url'] ? __('notifications.open') : __('notifications.mark_read') }}
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">{{ __('notifications.empty') }}</div>
        @endforelse
    </div>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>

@push('styles')
<style>
    .notification-inbox-item--unread {
        background-color: #fffdf5;
        border-left: 4px solid #ffc107;
    }
    .notification-inbox-body,
    .notification-inbox-sms {
        width: 100%;
        margin-bottom: 0;
        line-height: 1.5;
        text-align: left;
    }
</style>
@endpush
@endsection
