@php
    $assignLabel = !empty($canReassign) ? 'Reassign Traveler' : 'Assign Traveler';
@endphp

<div class="modal fade" id="assignTravelerModal" tabindex="-1" aria-labelledby="assignTravelerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTravelerModalLabel">
                    <i class="bi bi-truck me-2"></i>{{ $assignLabel }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(session('status') && request()->boolean('assign'))
                    <div class="alert alert-success py-2 small">{{ session('status') }}</div>
                @endif
                @error('error')
                    <div class="alert alert-danger py-2 small">{{ $message }}</div>
                @enderror

                <p class="text-muted small mb-3">
                    Order #{{ $order->id }} · <strong>{{ $orderQuantity }}</strong> item(s)
                    @if($order->delivery?->traveler)
                        · Current traveler: <strong>{{ $order->delivery->traveler->name }}</strong>
                    @endif
                </p>

                @if($availableTravelers->isNotEmpty())
                    <form method="POST" action="{{ route('chef.orders.assign-traveler', $order) }}" class="mb-4">
                        @csrf
                        <label class="form-label fw-semibold" for="chef_assign_traveler_id">Select traveler</label>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <select name="traveler_id" id="chef_assign_traveler_id" class="form-select" required>
                                <option value="">Choose traveler…</option>
                                @foreach($availableTravelers as $traveler)
                                    <option value="{{ $traveler->id }}" @selected($order->delivery && (int) $order->delivery->traveler_id === (int) $traveler->id)>
                                        {{ $traveler->name }}
                                        @if($traveler->travelerProfile?->vehicle_type)
                                            · {{ ucfirst($traveler->travelerProfile->vehicle_type) }}
                                        @endif
                                        @if($traveler->travelerProfile?->is_online)
                                            · Online
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-success flex-shrink-0">
                                <i class="bi bi-person-check"></i> {{ !empty($canReassign) ? 'Reassign' : 'Assign' }}
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-4">No approved travelers are available yet.</p>
                @endif

                @if($nearbyTravelers->isNotEmpty())
                    <h6 class="fw-semibold small text-uppercase text-muted mb-2">Recommended nearby (online)</h6>
                    <ul class="list-group list-group-flush">
                        @foreach($nearbyTravelers as $item)
                            @php $t = $item->user; @endphp
                            <li class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 px-0">
                                <div>
                                    <strong>{{ $t->name }}</strong>
                                    @if($item->recommended)
                                        <span class="badge bg-success ms-1">Best match</span>
                                    @endif
                                    <div class="small text-muted mt-1">
                                        {{ ucfirst($item->vehicle_type ?? 'vehicle') }}
                                        · cap {{ $item->vehicle_capacity }}
                                        @if($item->max_load_capacity) (max {{ $item->max_load_capacity }}) @endif
                                        · {{ $item->location_source === 'gps' ? 'Live GPS' : 'Registered address' }}
                                    </div>
                                    @if($item->distance_km_to_chef !== null || $item->distance_km_to_customer !== null)
                                        <div class="small text-muted">
                                            @if($item->distance_km_to_chef !== null) {{ number_format($item->distance_km_to_chef, 1) }} km to kitchen @endif
                                            @if($item->distance_km_to_customer !== null) · {{ number_format($item->distance_km_to_customer, 1) }} km to customer @endif
                                            @if($item->combined_km !== null) · <strong>{{ number_format($item->combined_km, 1) }} km total</strong> @endif
                                        </div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('chef.orders.assign-traveler', $order) }}" class="flex-shrink-0">
                                    @csrf
                                    <input type="hidden" name="traveler_id" value="{{ $t->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success w-100 w-sm-auto">
                                        {{ !empty($canReassign) ? 'Reassign' : 'Assign' }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @elseif($availableTravelers->isNotEmpty())
                    <p class="text-muted small mb-0">No online travelers matched automatically. Use the list above to assign manually.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
