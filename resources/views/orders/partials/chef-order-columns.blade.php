@php
    $chefCount = $chefGroups instanceof \Illuminate\Support\Collection
        ? $chefGroups->count()
        : count($chefGroups);
    $columnClass = match (true) {
        $chefCount <= 1 => 'col-12',
        $chefCount === 2 => 'col-md-6',
        default => 'col-md-4',
    };
    $showImages = $showImages ?? true;
@endphp
<div class="row g-3 chef-order-columns">
    @foreach($chefGroups as $group)
        @php
            $groupSubtotal = collect($group['items'])->sum(fn ($i) => (float) $i['line_total']);
        @endphp
        <div class="{{ $columnClass }}">
            <div class="chef-order-card card h-100 border-0 shadow-sm">
                <div class="card-header bg-success bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 text-success text-truncate" title="{{ $group['chef']->name }}">
                        <i class="bi bi-person-badge me-1"></i>{{ $group['chef']->name }}
                    </h6>
                </div>
                <div class="card-body p-3">
                    @foreach($group['items'] as $item)
                        <div class="chef-order-item {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                            @if($showImages && $item['meal']->image_url)
                                <div class="rounded overflow-hidden mb-2" style="height: 88px;">
                                    <img
                                        src="{{ $item['meal']->image_url }}"
                                        alt=""
                                        class="w-100 h-100 object-fit-cover"
                                    >
                                </div>
                            @endif
                            <div class="fw-semibold small">{{ $item['meal']->name }}</div>
                            <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
                                <span class="text-muted small">Qty: {{ $item['quantity'] }}</span>
                                <span class="fw-bold text-success small text-nowrap">TZS {{ number_format((float) $item['line_total'], 0) }}</span>
                            </div>
                        </div>
                    @endforeach
                    @if($chefCount > 1 && count($group['items']) > 1)
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top small">
                            <span class="text-muted">Chef subtotal</span>
                            <span class="fw-semibold">TZS {{ number_format($groupSubtotal, 0) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
