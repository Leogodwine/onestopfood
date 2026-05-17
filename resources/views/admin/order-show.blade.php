@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h2>
            <p class="text-muted mb-0">Full order details and admin controls</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-receipt"></i> Summary</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="fw-semibold" style="width: 180px;">Customer</td>
                            <td>{{ $order->customer->name ?? 'N/A' }} <br><small class="text-muted">{{ $order->customer->email ?? '' }}</small></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Chef</td>
                            <td>{{ $order->chef->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Total</td>
                            <td>
                                <span class="fw-bold">TZS {{ number_format((float)$order->total, 2) }}</span>
                                @if($order->delivery_fee > 0)
                                    <br><small class="text-muted">Includes delivery: TZS {{ number_format((float)$order->delivery_fee, 2) }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Status</td>
                            <td>{{ ucfirst(str_replace('_',' ',$order->status)) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Created</td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-basket"></i> Items</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Meal</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->meal->name ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>TZS {{ number_format((float)$item->unit_price, 2) }}</td>
                                <td>TZS {{ number_format((float)$item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-truck"></i> Delivery</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="fw-semibold" style="width: 180px;">Traveler</td>
                            <td>{{ $order->delivery?->traveler?->name ?? 'Unassigned' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Delivery Status</td>
                            <td>{{ $order->delivery?->status ?? 'unassigned' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle"></i> Admin Intervention</h5>
            </div>
            <div class="mb-3">
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small text-muted">Force Status Change</label>
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach(['pending','accepted','preparing','ready','out_for_delivery','delivered','cancelled'] as $s)
                                <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Reason (optional)</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Reason for override"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-arrow-repeat"></i> Update Status
                    </button>
                </form>
            </div>

            <hr>

            <div class="mb-3">
                <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small text-muted">Cancel Reason</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2" required placeholder="Reason for cancellation"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="bi bi-x-circle"></i> Cancel Order
                    </button>
                </form>
            </div>

            <hr>

            <div>
                <form method="POST" action="{{ route('admin.orders.reassign-traveler', $order) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small text-muted">Reassign Traveler</label>
                        <select name="traveler_id" class="form-select form-select-sm" required>
                            <option value="">Select traveler</option>
                            @foreach($availableTravelers as $traveler)
                                <option value="{{ $traveler->id }}" @selected($order->delivery && $order->delivery->traveler_id === $traveler->id)>
                                    {{ $traveler->name }} ({{ $traveler->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Reason (optional)</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="2" placeholder="Why are you reassigning?"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-arrow-left-right"></i> Reassign Traveler
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

