@php
    $selectedCity = old('city', $profile->city ?? '');
    $selectedDistrict = old('district', $profile->district ?? '');

    if ($selectedCity === '' && ! empty($profile->city_district ?? null)) {
        $legacy = $profile->city_district;
        $cities = config('tanzania.cities', []);

        if (array_key_exists($legacy, $cities)) {
            $selectedCity = $legacy;
        } else {
            foreach ($cities as $cityName => $districts) {
                if (in_array($legacy, $districts, true)) {
                    $selectedCity = $cityName;
                    $selectedDistrict = $legacy;
                    break;
                }
            }

            if ($selectedCity === '') {
                $selectedCity = $legacy;
            }
        }
    }

    $cities = array_keys(config('tanzania.cities', []));
    sort($cities);
@endphp

<div class="col-md-6">
    <label class="form-label">City / Region</label>
    <select name="city"
            id="city-select"
            class="form-select @error('city') is-invalid @enderror"
            required
            data-selected-city="{{ $selectedCity }}">
        <option value="">Select city...</option>
        @foreach($cities as $city)
            <option value="{{ $city }}" @selected($selectedCity === $city)>{{ $city }}</option>
        @endforeach
        @if($selectedCity && ! in_array($selectedCity, $cities, true))
            <option value="{{ $selectedCity }}" selected>{{ $selectedCity }}</option>
        @endif
    </select>
    @error('city')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6">
    <label class="form-label">District</label>
    <select name="district"
            id="district-select"
            class="form-select @error('district') is-invalid @enderror"
            required
            data-selected-district="{{ $selectedDistrict }}"
            @disabled($selectedCity === '')>
        <option value="">Select district...</option>
    </select>
    @error('district')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cityDistrictMap = @json(config('tanzania.cities'));
    const citySelect = document.getElementById('city-select');
    const districtSelect = document.getElementById('district-select');

    if (!citySelect || !districtSelect) {
        return;
    }

    function populateDistricts(city, selectedDistrict) {
        districtSelect.innerHTML = '<option value="">Select district...</option>';

        const districts = cityDistrictMap[city] || [];
        districts.forEach(function (district) {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            if (selectedDistrict && selectedDistrict === district) {
                option.selected = true;
            }
            districtSelect.appendChild(option);
        });

        if (selectedDistrict && !districts.includes(selectedDistrict)) {
            const legacyOption = document.createElement('option');
            legacyOption.value = selectedDistrict;
            legacyOption.textContent = selectedDistrict;
            legacyOption.selected = true;
            districtSelect.appendChild(legacyOption);
        }

        districtSelect.disabled = districts.length === 0 && !selectedDistrict;
    }

    citySelect.addEventListener('change', function () {
        populateDistricts(this.value, '');
        districtSelect.disabled = this.value === '';
    });

    const initialCity = citySelect.dataset.selectedCity || citySelect.value;
    const initialDistrict = districtSelect.dataset.selectedDistrict || '';

    if (initialCity) {
        citySelect.value = initialCity;
        populateDistricts(initialCity, initialDistrict);
    }
});
</script>
@endpush
@endonce
