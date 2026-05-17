@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Dispute #{{ $dispute->id }}</h2>
            <p class="text-muted mb-0">Review dispute details and resolution</p>
        </div>
        <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to Disputes
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Details</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="fw-semibold" style="width: 180px;">Order</td>
                            <td>
                                @if($dispute->order_id)
                                    #{{ str_pad($dispute->order_id, 6, '0', STR_PAD_LEFT) }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Complainant</td>
                            <td>{{ $dispute->createdBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Category</td>
                            <td>{{ ucfirst(str_replace('_',' ',$dispute->category ?? 'other')) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Status</td>
                            <td>{{ ucfirst(str_replace('_',' ',$dispute->status)) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Description</td>
                            <td>{{ $dispute->description }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($dispute->resolution_notes)
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-clipboard-check"></i> Resolution</h5>
                </div>
                <div class="p-3">
                    <p class="mb-1"><strong>Status:</strong> {{ ucfirst(str_replace('_',' ',$dispute->status)) }}</p>
                    <p class="mb-0 text-muted">{{ $dispute->resolution_notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-pen"></i> Update Dispute</h5>
            </div>
            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm" required>
                        @foreach(['open','in_review','resolved','escalated'] as $s)
                            <option value="{{ $s }}" @selected($dispute->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Resolution Notes</label>
                    <textarea name="resolution_notes" class="form-control form-control-sm" rows="4" required>{{ old('resolution_notes', $dispute->resolution_notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-check2-circle"></i> Save
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

