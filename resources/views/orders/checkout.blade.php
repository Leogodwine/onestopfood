@extends('layouts.dashboard')

@section('content')
<div class="checkout-page">
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>

        <!-- Step Indicator -->
        <div class="checkout-steps mb-5">
            <div class="step {{ $step >= 1 ? 'active' : '' }} {{ $step > 1 ? 'completed' : '' }}">
                <div class="step-number">1</div>
                <div class="step-label">Order Summary</div>
            </div>
            <div class="step {{ $step >= 2 ? 'active' : '' }} {{ $step > 2 ? 'completed' : '' }}">
                <div class="step-number">2</div>
                <div class="step-label">Delivery</div>
            </div>
            <div class="step {{ $step >= 3 ? 'active' : '' }} {{ $step > 3 ? 'completed' : '' }}">
                <div class="step-number">3</div>
                <div class="step-label">Review</div>
            </div>
            <div class="step {{ $step >= 4 ? 'active' : '' }} {{ $step > 4 ? 'completed' : '' }}">
                <div class="step-number">4</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step {{ $step >= 5 ? 'active' : '' }}">
                <div class="step-number">5</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                @if($step == 1)
                    <!-- Step 1: Order Summary -->
                    <div class="checkout-section">
                        <h3 class="section-title mb-4">ORDER SUMMARY</h3>
                        @include('partials.checkout-currency-select')
                        <div class="order-items mb-4">
                            @if(!empty($isMultiChef) && isset($chefGroups))
                                @include('orders.partials.chef-order-columns', ['chefGroups' => $chefGroups, 'checkoutCurrency' => $checkoutCurrency ?? null])
                            @else
                            <div class="row g-3">
                            @foreach($items as $item)
                                <div class="col-6 col-sm-6 col-md-4 col-lg-2">
                                    <div class="order-item card h-100 border-0 shadow-sm">
                                        <div class="card-body p-2">
                                            <div class="item-image mb-2 rounded overflow-hidden" style="height: 90px;">
                                                @if($item['meal']->image_url)
                                                    <img src="{{ $item['meal']->image_url }}" 
                                                         alt="{{ $item['meal']->name }}" 
                                                         class="w-100 h-100 object-fit-cover"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="bg-light w-100 h-100 d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="item-details">
                                                <div class="fw-semibold small text-truncate mb-1" title="{{ $item['meal']->name }}">{{ $item['meal']->name }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">Chef: {{ $item['meal']->chef?->name }}</div>
                                                @if($item['meal']->category)
                                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $item['meal']->category }}</div>
                                                @endif
                                                <div class="d-flex justify-content-between align-items-center mt-1 flex-wrap gap-1">
                                                    <span class="text-muted" style="font-size: 0.7rem;">Qty: {{ $item['quantity'] }}</span>
                                                    <span class="price fw-bold" style="font-size: 0.8rem;">{{ money($item['line_total'], $checkoutCurrency ?? null) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <a href="{{ route('orders.checkout', ['step' => 2]) }}" class="btn btn-success btn-lg">
                                Continue to Delivery <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                @elseif($step == 2)
                    <!-- Step 2: Delivery -->
                    <div class="checkout-section">
                        <h3 class="section-title mb-4">DELIVERY INFORMATION</h3>
                        @if($userLocations->isEmpty())
                            <div class="alert alert-warning mb-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Please add a delivery address to continue.
                                <button type="button" class="btn btn-success btn-sm mt-2" onclick="openAddAddressModal()">
                                    <i class="bi bi-plus-circle"></i> Add Delivery Address
                                </button>
                            </div>
                        @else
                            <form method="POST" action="{{ route('orders.checkout.delivery') }}">
                                @csrf
                                <div class="address-selection mb-4">
                                    @foreach($userLocations as $loc)
                                        <div class="address-card mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="delivery_location_id" 
                                                       id="location_{{ $loc->id }}" 
                                                       value="{{ $loc->id }}" 
                                                       {{ ($deliveryLocationId && $deliveryLocationId == $loc->id) || (!$deliveryLocationId && $loc->is_primary) ? 'checked' : '' }}
                                                       required>
                                                <label class="form-check-label w-100" for="location_{{ $loc->id }}">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            @if($loc->is_primary)
                                                                <span class="badge bg-success mb-2">Primary</span>
                                                            @endif
                                                            @if($loc->label)
                                                                <div class="fw-semibold mb-1">{{ $loc->label }}</div>
                                                            @endif
                                                            <div class="text-muted small">
                                                                {{ $loc->address_line }}<br>
                                                                @if($loc->city || $loc->region)
                                                                    {{ $loc->city }}{{ $loc->region ? ', ' . $loc->region : '' }}<br>
                                                                @endif
                                                                @if($loc->country)
                                                                    {{ $loc->country }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mb-4">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddAddressModal()">
                                        <i class="bi bi-plus-circle"></i> Add New Address
                                    </button>
                                    <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
                                        <i class="bi bi-gear"></i> Manage Addresses
                                    </a>
                                    @if($deliveryLocation)
                                        <a href="https://wa.me/255651490677?text={{ urlencode('My delivery address: ' . $deliveryLocation->address_line . ($deliveryLocation->city ? ', ' . $deliveryLocation->city : '') . ($deliveryLocation->region ? ', ' . $deliveryLocation->region : '') . ($deliveryLocation->country ? ', ' . $deliveryLocation->country : '')) }}" 
                                           target="_blank" 
                                           rel="noopener"
                                           class="btn btn-outline-success btn-sm ms-2">
                                            <i class="bi bi-whatsapp"></i> Share location to WhatsApp
                                        </a>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('orders.checkout', ['step' => 1]) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        Continue to Review <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                @elseif($step == 3)
                    <!-- Step 3: Review Delivery -->
                    <div class="checkout-section">
                        <h3 class="section-title mb-4">REVIEW DELIVERY INFORMATION</h3>
                        @if($deliveryLocation)
                            <div class="card border-success mb-4">
                                <div class="card-body">
                                    <h5 class="mb-3"><i class="bi bi-check-circle text-success"></i> Delivery Address</h5>
                                    @if($deliveryLocation->label)
                                        <div class="fw-semibold mb-2">{{ $deliveryLocation->label }}</div>
                                    @endif
                                    <div class="text-muted">
                                        {{ $deliveryLocation->address_line }}<br>
                                        @if($deliveryLocation->city || $deliveryLocation->region)
                                            {{ $deliveryLocation->city }}{{ $deliveryLocation->region ? ', ' . $deliveryLocation->region : '' }}<br>
                                        @endif
                                        @if($deliveryLocation->country)
                                            {{ $deliveryLocation->country }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('orders.checkout', ['step' => 2]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Change Address
                                </a>
                                <a href="{{ route('orders.checkout', ['step' => 4]) }}" class="btn btn-success btn-lg">
                                    Confirm & Proceed to Payment <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Please select a delivery address.
                                <a href="{{ route('orders.checkout', ['step' => 2]) }}" class="btn btn-sm btn-success mt-2">Go to Delivery</a>
                            </div>
                        @endif
                    </div>

                @elseif($step == 4)
                    <!-- Step 4: Payment -->
                    <div class="checkout-section">
                        <h3 class="section-title mb-4">PAYMENT METHOD</h3>
                        <form method="POST" action="{{ route('orders.checkout.payment') }}" id="paymentForm">
                            @csrf
                            <input type="hidden" name="delivery_location_id" value="{{ $deliveryLocationId ?? $deliveryLocation->id ?? '' }}">
                            <div class="payment-methods mb-4">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" {{ ($paymentMethod ?? '') === 'mpesa' || !isset($paymentMethod) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="mpesa">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-phone-fill text-success me-3 fs-5"></i>
                                            <div>
                                                <div class="fw-semibold">M-Pesa</div>
                                                <small class="text-muted">Mobile money payment</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="tigo" value="tigo" {{ ($paymentMethod ?? '') === 'tigo' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="tigo">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-phone-fill text-primary me-3 fs-5"></i>
                                            <div>
                                                <div class="fw-semibold">Tigo Pesa</div>
                                                <small class="text-muted">Mobile money payment</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="airtel" value="airtel" {{ ($paymentMethod ?? '') === 'airtel' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="airtel">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-phone-fill text-danger me-3 fs-5"></i>
                                            <div>
                                                <div class="fw-semibold">Airtel Money</div>
                                                <small class="text-muted">Mobile money payment</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" {{ ($paymentMethod ?? '') === 'card' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="card">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-credit-card-2-front text-info me-3 fs-5"></i>
                                            <div>
                                                <div class="fw-semibold">Card Payment</div>
                                                <small class="text-muted">Credit/Debit card</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" {{ ($paymentMethod ?? '') === 'cod' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="cod">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-coin text-warning me-3 fs-5"></i>
                                            <div>
                                                <div class="fw-semibold">Cash on Delivery</div>
                                                <small class="text-muted">Pay when you receive</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div id="paymentDetails" class="mb-4" style="display: {{ ($paymentMethod ?? '') && ($paymentMethod ?? '') !== 'cod' ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <label for="payment_phone" class="form-label">Mobile money number <span id="paymentPhoneRequired" class="text-danger" style="display:none;">*</span></label>
                                    <input type="text" class="form-control" name="payment_phone" id="payment_phone" placeholder="e.g. 255712345678" value="{{ $paymentPhone ?? old('payment_phone', auth()->user()->phone) }}">
                                    <small class="text-muted" id="paymentPhoneHint">Required for M-Pesa, Tigo Pesa, or Airtel Money — you will receive a PIN prompt on this number.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_reference" class="form-label">Payment Reference (Optional)</label>
                                    <input type="text" class="form-control" name="payment_reference" id="payment_reference" placeholder="Transaction ID or reference" value="{{ $paymentReference ?? old('payment_reference') }}">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="special_instructions" class="form-label fw-semibold">Special Instructions (Optional)</label>
                                <textarea class="form-control" name="special_instructions" id="special_instructions" rows="3" placeholder="Any special delivery instructions...">{{ $specialInstructions ?? old('special_instructions') }}</textarea>
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger mb-4">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('orders.checkout', ['step' => 3]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    Continue to Confirm <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                @elseif($step == 5)
                    <!-- Step 5: Confirm & Place Order -->
                    <div class="checkout-section">
                        <h3 class="section-title mb-4">CONFIRM & PLACE ORDER</h3>
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            @if(!empty($isMultiChef))
                                You will place <strong>{{ $chefCount }} separate orders</strong> (one per chef). Each chef prepares only their meals; delivery is scheduled per order.
                            @else
                                Please review your order details below. Once you confirm, you will receive an email and SMS confirmation.
                            @endif
                            <span class="d-block mt-2 small">{{ __('payments.order_placed_unpaid') }}</span>
                        </div>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="mb-3">Order Summary</h5>
                                <div class="mb-3">
                                    @foreach($items as $item)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>{{ $item['meal']->name }} x{{ $item['quantity'] }}</span>
                                            <span>{{ money($item['line_total'], $checkoutCurrency ?? null) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total</strong>
                                    <strong class="text-success">{{ money($total, $checkoutCurrency ?? null) }}</strong>
                                </div>
                            </div>
                        </div>
                        @if($deliveryLocation)
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="mb-2">Delivery Address</h5>
                                    <div class="text-muted">
                                        {{ $deliveryLocation->address_line }}<br>
                                        @if($deliveryLocation->city || $deliveryLocation->region)
                                            {{ $deliveryLocation->city }}{{ $deliveryLocation->region ? ', ' . $deliveryLocation->region : '' }}<br>
                                        @endif
                                        @if($deliveryLocation->country)
                                            {{ $deliveryLocation->country }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('orders.place') }}" id="placeOrderForm">
                            @csrf
                            <input type="hidden" name="delivery_location_id" value="{{ $deliveryLocationId ?? $deliveryLocation->id ?? '' }}">
                            <input type="hidden" name="payment_method" id="confirm_payment_method" value="{{ old('payment_method', $paymentMethod ?? 'mpesa') }}">
                            <input type="hidden" name="special_instructions" id="confirm_special_instructions" value="{{ old('special_instructions', $specialInstructions ?? '') }}">
                            <input type="hidden" name="payment_phone" id="confirm_payment_phone" value="{{ old('payment_phone', $paymentPhone ?? '') }}">
                            <input type="hidden" name="payment_reference" id="confirm_payment_reference" value="{{ old('payment_reference', $paymentReference ?? '') }}">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('orders.checkout', ['step' => 4]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-success btn-lg" id="placeOrderBtn">
                                    <i class="bi bi-check-circle"></i>
                                    @if(!empty($isMultiChef))
                                        PLACE {{ $chefCount }} ORDERS
                                    @else
                                        PLACE ORDER
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar: Order Summary (always visible) -->
            <div class="col-lg-4">
                <div class="order-summary-card">
                    <h3 class="section-title mb-3 mb-md-4">ORDER SUMMARY</h3>
                    @include('partials.checkout-currency-select')
                    <div class="order-items mb-3 mb-md-4">
                        @foreach($items as $item)
                            <div class="order-item-small d-flex mb-2 pb-2 border-bottom">
                                <div class="item-image-small me-2">
                                    @if($item['meal']->image_url)
                                        <img src="{{ $item['meal']->image_url }}" 
                                             alt="{{ $item['meal']->name }}" 
                                             class="rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted" style="font-size: 0.75rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-details-small flex-grow-1">
                                    <div class="fw-semibold small mb-0">{{ $item['meal']->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">x{{ $item['quantity'] }}</div>
                                </div>
                                <div class="price small">{{ money($item['line_total'], $checkoutCurrency ?? null) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="price-breakdown">
                        <div class="d-flex justify-content-between mb-1 mb-md-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="price">{{ money($subtotal, $checkoutCurrency ?? null) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 mb-md-2">
                            <span class="text-muted">
                                Delivery Fee
                                @if(!empty($isMultiChef))
                                    ({{ $chefCount }} chefs)
                                @endif
                            </span>
                            <span class="price">
                                @if($deliveryFee > 0)
                                    {{ money($deliveryFee, $checkoutCurrency ?? null) }}
                                @else
                                    <span class="text-success">Free</span>
                                @endif
                            </span>
                        </div>
                        @if(!empty($isMultiChef))
                            <div class="small text-muted mb-2">Separate delivery per chef ({{ money($deliveryFeePerChef ?? 0, $checkoutCurrency ?? null) }} each)</div>
                        @endif
                        <hr class="my-2 my-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-6">Total</span>
                            <span class="price fs-5 text-success">{{ money($total, $checkoutCurrency ?? null) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}
.checkout-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}
.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
}
.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    border: 2px solid #e9ecef;
}
.step.active .step-number {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
}
.step.completed .step-number {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
}
.step.completed .step-number::after {
    content: '✓';
}
.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}
.step.active .step-label {
    color: #28a745;
    font-weight: 600;
}
.checkout-section {
    background: #ffffff;
    padding: 2rem;
    border-radius: 8px;
    border: 1px solid #e5e5e5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.section-title {
    font-family: var(--font-headline);
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #212529;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e9ecef;
}
.address-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
}
.address-card:hover {
    border-color: #28a745;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.1);
}
.payment-option {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
    cursor: pointer;
}
.payment-option:hover {
    border-color: #28a745;
    background-color: #f8f9fa;
}
.payment-option .form-check-input:checked ~ .form-check-label {
    color: #28a745;
}
.order-summary-card {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 2rem;
    position: sticky;
    top: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.price {
    font-family: var(--font-body);
    font-weight: 700;
    color: #212529;
}
.chef-order-card .card-header h6 {
    font-size: 0.9rem;
}
.chef-order-columns .chef-order-item:last-child {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
    border-bottom: none !important;
}
@media (max-width: 767.98px) {
    .chef-order-columns .col-md-4,
    .chef-order-columns .col-md-6 {
        margin-bottom: 0.375rem;
    }
    .order-summary-card {
        position: relative;
        top: 0;
        margin-top: 0.75rem;
    }
    .checkout-steps {
        flex-wrap: wrap;
        gap: 0.625rem;
    }
    .checkout-steps::before {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    const placeOrderForm = document.getElementById('placeOrderForm');
    const paymentDetails = document.getElementById('paymentDetails');
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    
    const paymentPhone = document.getElementById('payment_phone');
    const phoneRequiredMark = document.getElementById('paymentPhoneRequired');

    const mobileMethods = ['mpesa', 'tigo', 'airtel'];

    function syncPaymentPhoneRequired() {
        const method = document.querySelector('input[name="payment_method"]:checked');
        const needsPhone = method && mobileMethods.includes(method.value);
        if (paymentPhone) {
            paymentPhone.required = needsPhone;
        }
        if (phoneRequiredMark) {
            phoneRequiredMark.style.display = needsPhone ? 'inline' : 'none';
        }
    }

    if (paymentMethods.length) {
        paymentMethods.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value !== 'cod') {
                    paymentDetails.style.display = 'block';
                } else {
                    paymentDetails.style.display = 'none';
                }
                syncPaymentPhoneRequired();
            });
        });
        syncPaymentPhoneRequired();
    }
    
    if (placeOrderForm) {
        placeOrderForm.addEventListener('submit', function(e) {
            const methodField = document.getElementById('confirm_payment_method');
            if (methodField && !methodField.value) {
                const checked = document.querySelector('input[name="payment_method"]:checked');
                if (checked) {
                    methodField.value = checked.value;
                }
            }
            const btn = document.getElementById('placeOrderBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Placing Order...';
        });
    }
});
</script>

@include('locations._modal')
@endsection
