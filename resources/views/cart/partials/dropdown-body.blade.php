@php
    $cartByChef = collect($cartItems)->groupBy(fn ($item) => $item['meal']->chef_id ?? 0);
    $multiChefCart = $cartByChef->count() > 1;
@endphp
@if($multiChefCart)
    <div class="cart-dropdown-body cart-dropdown-body--multi-chef">
        <div class="row g-2 p-2 mx-0">
            @foreach($cartByChef as $chefItems)
                @php
                    $chef = $chefItems->first()['meal']->chef;
                    $colClass = match ($cartByChef->count()) {
                        2 => 'col-6',
                        default => 'col-4',
                    };
                @endphp
                <div class="{{ $colClass }}">
                    <div class="cart-chef-column h-100 border rounded p-2">
                        <div class="cart-chef-column-name fw-semibold small text-success text-truncate mb-2" title="{{ $chef?->name }}">
                            {{ $chef?->name ?? __('common.chef') }}
                        </div>
                        @foreach($chefItems as $item)
                            <div class="cart-chef-column-item {{ !$loop->last ? 'mb-2 pb-2 border-bottom' : '' }}">
                                <div class="small fw-medium text-truncate" title="{{ $item['meal']->name }}">{{ $item['meal']->name }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-1 gap-1 flex-wrap">
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ __('nav.qty') }}: {{ $item['quantity'] }}</span>
                                    <span class="fw-semibold text-success text-nowrap" style="font-size: 0.75rem;">{{ money($item['line_total']) }}</span>
                                </div>
                                <form method="POST" action="{{ route('cart.remove', $item['meal']) }}" class="mt-1" onsubmit="return confirm(@json(__('common.remove_item_confirm')));">
                                    @csrf
                                    <button class="btn btn-link btn-sm text-danger p-0" type="submit" style="font-size: 0.7rem;">{{ __('common.remove') }}</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="cart-dropdown-body">
        @foreach($cartItems as $item)
            <div class="cart-dropdown-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">{{ $item['meal']->name }}</div>
                    @if($item['meal']->chef)
                        <div class="cart-item-chef">{{ $item['meal']->chef->name }}</div>
                    @endif
                </div>
                <div class="cart-item-details">
                    <div class="cart-item-qty">{{ __('nav.qty') }}: {{ $item['quantity'] }}</div>
                    <div class="cart-item-total">{{ money($item['line_total']) }}</div>
                </div>
                <form method="POST" action="{{ route('cart.remove', $item['meal']) }}" class="d-inline" onsubmit="return confirm(@json(__('common.remove_item_confirm')));">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger cart-item-remove" type="submit" title="{{ __('common.remove') }}">
                        <i class="bi bi-x"></i>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif
