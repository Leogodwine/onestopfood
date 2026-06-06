@php
    $countryCodeName = $countryCodeName ?? 'phone_country_code';
    $numberName = $numberName ?? 'phone_number';
    $inputId = $inputId ?? 'phone_number';
    $selectId = $selectId ?? 'phone_country_code';
    $label = $label ?? __('auth.phone_label');
    $required = $required ?? true;
    $size = $size ?? 'sm';
    $errorBag = $errorBag ?? null;
    $fullValue = $value ?? '';
    $split = \App\Support\PhoneNumber::split($fullValue);
    $selectedCode = old($countryCodeName, $countryCode ?? $split['country_code']);
    $nationalValue = old($numberName, $national ?? $split['national']);
    $countries = $phoneCountries ?? \App\Support\PhoneNumber::countries();
    $controlClass = $size === 'lg' ? 'form-control form-control-lg' : 'form-control form-control-sm';
    $triggerClass = $size === 'lg' ? 'form-select form-select-lg' : 'form-select form-select-sm';
    $selectedDisplay = \App\Support\PhoneNumber::countryDisplay((string) $selectedCode);
    $selectedLength = \App\Support\PhoneNumber::nationalLength((string) $selectedCode);
    $selectedPlaceholder = $placeholder ?? \App\Support\PhoneNumber::nationalPlaceholder((string) $selectedCode);
    $selectedPattern = '[1-9][0-9]{'.($selectedLength - 1).'}';
    $selectedTitle = \App\Support\PhoneNumber::invalidNationalMessage((string) $selectedCode);
    $phoneRulesJson = json_encode(\App\Support\PhoneNumber::frontendRules(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $mergedKeys = array_values(array_unique(array_filter([
        'phone',
        'payment_phone',
        'emergency_contact_phone',
        $numberName,
    ])));
    $hasCountryError = $errorBag
        ? $errors->{$errorBag}->has($countryCodeName)
        : $errors->has($countryCodeName);
    $hasNumberError = $errorBag
        ? collect($mergedKeys)->contains(fn ($key) => $errors->{$errorBag}->has($key))
            || $errors->{$errorBag}->has($numberName)
        : collect($mergedKeys)->contains(fn ($key) => $errors->has($key))
            || $errors->has($numberName);
    $countryError = $errorBag
        ? $errors->{$errorBag}->first($countryCodeName)
        : $errors->first($countryCodeName);
    $numberError = null;
    if ($errorBag) {
        foreach ($mergedKeys as $mergedKey) {
            if ($errors->{$errorBag}->has($mergedKey)) {
                $numberError = $errors->{$errorBag}->first($mergedKey);
                break;
            }
        }
        $numberError = $numberError ?: $errors->{$errorBag}->first($numberName);
    } else {
        foreach ($mergedKeys as $mergedKey) {
            if ($errors->has($mergedKey)) {
                $numberError = $errors->first($mergedKey);
                break;
            }
        }
        $numberError = $numberError ?: $errors->first($numberName);
    }
    $phoneError = $countryError ?: $numberError;
    $hasPhoneError = $hasCountryError || $hasNumberError || (bool) $phoneError;
    if ($phoneError) {
        $hasNumberError = true;
    }
@endphp

<label class="form-label fw-semibold mb-1" for="{{ $inputId }}">
    @if(!empty($labelIcon))
        <i class="bi {{ $labelIcon }}"></i>
    @endif
    {!! $label !!}
    @if($required)
        <span class="text-danger">*</span>
    @endif
</label>
<div class="input-group phone-input-group" data-phone-rules="{{ $phoneRulesJson }}">
    <div
        class="phone-country-picker @if($hasCountryError) is-invalid @endif"
        data-phone-country-picker
    >
        <input
            type="hidden"
            name="{{ $countryCodeName }}"
            value="{{ $selectedCode }}"
            data-phone-country-input
            @if($required) required @endif
        >
        <button
            type="button"
            class="{{ $triggerClass }} phone-country-trigger @if($hasCountryError) is-invalid @endif"
            id="{{ $selectId }}"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-label="{{ __('auth.phone_country_code_label') }}"
        >
            <span class="phone-country-flag" data-phone-country-flag aria-hidden="true">{{ $selectedDisplay['flag'] }}</span>
            <span class="phone-country-dial" data-phone-country-dial>{{ $selectedDisplay['dial'] }}</span>
            <i class="bi bi-chevron-down phone-country-chevron" aria-hidden="true"></i>
        </button>
        <div class="phone-country-menu" role="listbox" aria-label="{{ __('auth.phone_country_code_label') }}" hidden>
            @foreach($countries as $dialCode => $meta)
                @php
                    $display = \App\Support\PhoneNumber::countryDisplay((string) $dialCode);
                    $isSelected = (string) $selectedCode === (string) $dialCode;
                @endphp
                <button
                    type="button"
                    class="phone-country-option @if($isSelected) is-active @endif"
                    role="option"
                    aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                    data-value="{{ $dialCode }}"
                    data-flag="{{ $display['flag'] }}"
                    data-dial="{{ $display['dial'] }}"
                >
                    <span class="phone-country-flag" aria-hidden="true">{{ $display['flag'] }}</span>
                    <span class="phone-country-name">{{ $display['label'] }}</span>
                    <span class="phone-country-code">({{ $display['dial'] }})</span>
                </button>
            @endforeach
        </div>
    </div>
    <input
        type="tel"
        name="{{ $numberName }}"
        id="{{ $inputId }}"
        class="{{ $controlClass }} @if($hasNumberError) is-invalid @endif"
        value="{{ $nationalValue }}"
        inputmode="numeric"
        autocomplete="tel-national"
        data-phone-national-input
        placeholder="{{ $selectedPlaceholder }}"
        title="{{ $selectedTitle }}"
        @if($required) required @endif
        pattern="{{ $selectedPattern }}"
        maxlength="{{ $selectedLength }}"
    >
</div>
@if($hasPhoneError && $phoneError)
    <div class="invalid-feedback d-block">{{ $phoneError }}</div>
@endif
