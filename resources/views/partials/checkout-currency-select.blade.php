@php
    $checkoutCurrency = $checkoutCurrency ?? session('checkout_currency', display_currency());
    $currencyOptions = $currencyOptions ?? app(\App\Services\CurrencyService::class)->supported();
@endphp
<div class="checkout-currency-select mb-3 p-3 border rounded bg-light">
    <label for="checkout-currency" class="form-label fw-semibold small mb-2">
        <i class="bi bi-currency-exchange"></i> {{ __('common.display_currency') }}
    </label>
    <form method="GET" action="{{ route('currency.switch', ['currency' => '__CODE__']) }}" id="checkoutCurrencyForm" class="d-flex flex-column gap-2">
        <select id="checkout-currency" name="currency" class="form-select form-select-sm"
                onchange="window.location.href=this.dataset.base.replace('__CODE__', this.value)">
            @foreach($currencyOptions as $code => $meta)
                <option value="{{ $code }}" @selected($checkoutCurrency === $code)>
                    {{ $meta['label'] }} ({{ $code }})
                </option>
            @endforeach
        </select>
        <small class="text-muted mb-0">
            {{ __('common.currency_checkout_note') }}
        </small>
        @if($checkoutCurrency !== 'TZS')
            <small class="text-muted mb-0">
                {{ __('common.currency_charge_tzs', ['amount' => money($total ?? 0, 'TZS')]) }}
            </small>
        @endif
    </form>
</div>
<script>
(function () {
    var sel = document.getElementById('checkout-currency');
    if (!sel) return;
    var base = @json(route('currency.switch', ['currency' => '__CODE__']) . '?checkout=1');
    sel.dataset.base = base;
})();
</script>
