@php
    $appToasts = [];

    $cartAddedQty = session()->pull('cart_added_qty');
    if ($cartAddedQty) {
        $cartMessage = match ((int) $cartAddedQty) {
            1 => '1 item added to cart',
            2 => '2 items added to cart',
            3 => '3 items added to cart',
            default => ((int) $cartAddedQty).' items added to cart',
        };

        $appToasts[] = [
            'message' => $cartMessage,
            'icon' => 'bi-cart-check-fill',
            'type' => 'success',
        ];
    }

    $statusMessage = session('status');
    if ($statusMessage && $statusMessage !== 'Added to cart') {
        $appToasts[] = [
            'message' => $statusMessage,
            'icon' => 'bi-check-circle-fill',
            'type' => 'success',
        ];
    }

    if ($successMessage = session('success')) {
        $appToasts[] = [
            'message' => $successMessage,
            'icon' => 'bi-check-circle-fill',
            'type' => 'success',
        ];
    }
@endphp

@if($appToasts !== [])
    <script id="appToastData" type="application/json">{!! json_encode($appToasts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endif
