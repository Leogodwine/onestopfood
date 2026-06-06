@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Verification status</h2>
        <div class="page-header-actions">
            <a href="{{ route('verification.show') }}" class="btn btn-sm btn-outline-primary page-header-action-btn">
                <i class="bi bi-pencil-square"></i> Update profile
            </a>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary page-header-action-btn">
                <i class="bi bi-bell"></i> Notifications
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Track your personal details, uploaded documents, and approval steps on OneStopFood.</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="dashboard-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-check"></i> Approval steps</h5>
            </div>
            <div class="card-body">
                <div class="verification-steps">
                    @foreach($steps as $index => $step)
                        @php
                            $status = $step['status'];
                            $icon = match ($status) {
                                'completed' => 'bi-check-circle-fill text-success',
                                'in_progress' => 'bi-hourglass-split text-warning',
                                'rejected' => 'bi-x-circle-fill text-danger',
                                default => 'bi-circle text-muted',
                            };
                        @endphp
                        <div class="verification-step d-flex gap-3 {{ $index < count($steps) - 1 ? 'pb-3 mb-3 border-bottom' : '' }}">
                            <div class="pt-1"><i class="bi {{ $icon }} fs-5"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $step['label'] }}</div>
                                <div class="small text-muted">{{ $step['description'] }}</div>
                                @if(!empty($step['completed_at']))
                                    <div class="small text-muted mt-1">
                                        {{ $step['completed_at'] instanceof \Illuminate\Support\Carbon ? $step['completed_at']->timezone(config('app.timezone'))->format('M j, Y g:i a') : '' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person-vcard"></i> Personal details on file</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    @foreach($personalDetails as $key => $value)
                        @if(filled($value) && ! in_array($key, ['created_at', 'approved_at'], true))
                            <dt class="col-sm-4 text-muted text-capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                            <dd class="col-sm-8">{{ is_object($value) && method_exists($value, 'format') ? $value->format('M j, Y') : $value }}</dd>
                        @endif
                    @endforeach
                </dl>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-folder-check"></i> Uploaded documents</h5>
            </div>
            <div class="table-responsive table-responsive-fit">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Status</th>
                            <th class="text-end">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $doc['label'] }}</div>
                                    @if($doc['document_no'])
                                        <small class="text-muted">No: {{ $doc['document_no'] }}</small>
                                    @endif
                                    @if($doc['admin_notes'])
                                        <div class="small text-danger mt-1">{{ $doc['admin_notes'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $doc['status'] === 'approved' ? 'success' : ($doc['status'] === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($doc['status']) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($doc['url'])
                                        <a href="{{ $doc['url'] }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No documents uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
