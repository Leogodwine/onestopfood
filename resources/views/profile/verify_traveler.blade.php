@extends('layouts.dashboard')

@section('content')
<div class="page-header mb-4">
    <h2 class="fw-bold">Traveler Verification & Onboarding</h2>
    <p class="text-muted">Please complete all required sections to verify your traveler (delivery) account.</p>
</div>

    <!-- Profile Completion Progress Bar -->
    <div class="card shadow-sm border-0 p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold small text-muted">Onboarding Progress</span>
            <span class="fw-bold text-primary small" id="progressText">0%</span>
        </div>
        <div class="progress" style="height: 8px;">
            <div id="onboardingProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

<form action="{{ route('verification.submit') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <h6 class="fw-bold"><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <input type="hidden" name="active_tab" id="activeTabInput" value="{{ session('active_tab') ?? old('active_tab', '#tab-identity') }}">

    <div class="row g-4">
        <!-- Sidebar Navigation (Tabs) -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="list-group list-group-flush" id="verificationTabs" role="tablist">
                    <button class="list-group-item list-group-item-action active py-3" id="identity-tab" data-bs-toggle="list" href="#tab-identity" role="tab">
                        <i class="bi bi-person-badge me-2"></i> 1. Identity & Contact
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="address-tab" data-bs-toggle="list" href="#tab-address" role="tab">
                        <i class="bi bi-house me-2"></i> 2. Home Location
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="driving-tab" data-bs-toggle="list" href="#tab-driving" role="tab">
                        <i class="bi bi-card-checklist me-2"></i> 3. Driving License
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="vehicle-tab" data-bs-toggle="list" href="#tab-vehicle" role="tab">
                        <i class="bi bi-truck me-2"></i> 4. Vehicle Details
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="preferences-tab" data-bs-toggle="list" href="#tab-preferences" role="tab">
                        <i class="bi bi-gear me-2"></i> 5. Preferences
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="banking-tab" data-bs-toggle="list" href="#tab-banking" role="tab">
                        <i class="bi bi-bank me-2"></i> 6. Payout Details
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="legal-tab" data-bs-toggle="list" href="#tab-legal" role="tab">
                        <i class="bi bi-shield-check me-2"></i> 7. Compliance
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- 1. Identity & Contact -->
                <div class="tab-pane fade show active" id="tab-identity" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Identity & Contact Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name (as per ID)</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $profile->dob?->format('Y-m-d')) }}" max="{{ date('Y-m-d', strtotime('-18 years')) }}" required>
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIDA / ID / Passport Number</label>
                                <input type="text" name="nida_id" class="form-control @error('nida_id') is-invalid @enderror" value="{{ old('nida_id', $profile->nida_id) }}" required>
                                @error('nida_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <input type="text" name="nationality" class="form-control @error('nationality') is-invalid @enderror" value="{{ old('nationality', $profile->nationality) }}" required>
                                @error('nationality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select...</option>
                                    <option value="male" @selected(old('gender', $profile->gender) === 'male')>Male</option>
                                    <option value="female" @selected(old('gender', $profile->gender) === 'female')>Female</option>
                                    <option value="other" @selected(old('gender', $profile->gender) === 'other')>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Live Selfie Photo</label>
                                @include('profile.partials.selfie-capture', ['profile' => $profile, 'user' => $user])
                            </div>
                            <hr class="my-4">
                            <h6 class="fw-bold mb-2">Emergency Contact</h6>
                            <div class="col-md-4">
                                <label class="form-label">Contact Name</label>
                                <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relationship</label>
                                <input type="text" name="emergency_contact_relationship" class="form-control" value="{{ old('emergency_contact_relationship', $profile->emergency_contact_relationship) }}" required>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-address">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Home Address -->
                <div class="tab-pane fade" id="tab-address" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Home Address (Base Location)</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="street_address" class="form-control @error('street_address') is-invalid @enderror" value="{{ old('street_address', $profile->street_address) }}" required>
                                @error('street_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @include('profile.partials.city-district-select', ['profile' => $profile])
                            <div class="col-md-6">
                                <label class="form-label">Ward/Neighborhood</label>
                                <input type="text" name="ward_neighborhood" class="form-control" value="{{ old('ward_neighborhood', $profile->ward_neighborhood) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address Type</label>
                                <select name="address_type" class="form-select" required>
                                    <option value="Owned" @selected(old('address_type', $profile->address_type) === 'Owned')>Owned</option>
                                    <option value="Rented" @selected(old('address_type', $profile->address_type) === 'Rented')>Rented</option>
                                    <option value="Family" @selected(old('address_type', $profile->address_type) === 'Family')>Family Home</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Proof of Address</label>
                                <input type="file" name="proof_of_address" class="form-control">
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-driving">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Driving License -->
                <div class="tab-pane fade" id="tab-driving" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Driving License Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">License Number</label>
                                <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $profile->license_number) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">License Class/Category</label>
                                <input type="text" name="license_class" class="form-control" value="{{ old('license_class', $profile->license_class) }}" required placeholder="e.g. A, B, C">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Issue Date</label>
                                <input type="date" name="license_issue_date" class="form-control" value="{{ old('license_issue_date', $profile->license_issue_date?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="license_expiry_date" class="form-control" value="{{ old('license_expiry_date', $profile->license_expiry_date?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Accident / Violation History (If any)</label>
                                <textarea name="accident_violation_history" class="form-control" rows="2">{{ old('accident_violation_history', $profile->accident_violation_history) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-vehicle">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. Vehicle Details -->
                <div class="tab-pane fade" id="tab-vehicle" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Vehicle Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicle_type" class="form-select" required>
                                    <option value="Motorcycle" @selected(old('vehicle_type', $profile->vehicle_type) === 'Motorcycle')>Motorcycle</option>
                                    <option value="Car" @selected(old('vehicle_type', $profile->vehicle_type) === 'Car')>Car</option>
                                    <option value="Bicycle" @selected(old('vehicle_type', $profile->vehicle_type) === 'Bicycle')>Bicycle</option>
                                    <option value="Scooter" @selected(old('vehicle_type', $profile->vehicle_type) === 'Scooter')>Scooter</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Number</label>
                                <input type="text" name="vehicle_reg_no" class="form-control" value="{{ old('vehicle_reg_no', $profile->vehicle_reg_no) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Make</label>
                                <input type="text" name="vehicle_make" class="form-control" value="{{ old('vehicle_make', $profile->vehicle_make) }}" required placeholder="e.g. Honda">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model', $profile->vehicle_model) }}" required placeholder="e.g. PCX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" name="vehicle_color" class="form-control" value="{{ old('vehicle_color', $profile->vehicle_color) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vehicle Photo</label>
                                <input type="file" name="vehicle_photo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Proof of Ownership</label>
                                <input type="file" name="vehicle_proof_of_ownership" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Insurance Certificate</label>
                                <input type="file" name="vehicle_insurance" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Insurance Expiry Date</label>
                                <input type="date" name="vehicle_insurance_expiry" class="form-control" value="{{ old('vehicle_insurance_expiry', $profile->vehicle_insurance_expiry?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-preferences">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 5. Preferences -->
                <div class="tab-pane fade" id="tab-preferences" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Delivery Preferences</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Delivery Radius (km)</label>
                                <input type="number" name="delivery_radius" class="form-control" value="{{ old('delivery_radius', $profile->delivery_radius) }}" placeholder="e.g. 10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Zones (Up to 3)</label>
                                <input type="text" name="preferred_zones" class="form-control" value="{{ old('preferred_zones', $profile->preferred_zones) }}" placeholder="e.g. Kinondoni, Ilala">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Availability Schedule</label>
                                <textarea name="availability_schedule" class="form-control" rows="2" placeholder="e.g. Mon-Fri 8am-8pm">{{ old('availability_schedule', $profile->availability_schedule) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="weekend_availability" id="weekendCheck" @checked(old('weekend_availability', $profile->weekend_availability))>
                                    <label class="form-check-label" for="weekendCheck">
                                        I am available on weekends.
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-banking">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 6. Banking -->
                <div class="tab-pane fade" id="tab-banking" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Banking and Payout Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile->bank_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $profile->account_holder_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $profile->account_number) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Money Number</label>
                                <input type="text" name="mobile_money_number" class="form-control" value="{{ old('mobile_money_number', $profile->mobile_money_number) }}">
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-legal">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 7. Legal -->
                <div class="tab-pane fade" id="tab-legal" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Legal Compliance & Profile</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="background_check_consent" id="l1" required @checked(old('background_check_consent', $profile->background_check_consent))>
                                    <label class="form-check-label" for="l1">Consent to background check</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="tos_agreement" id="l2" required @checked(old('tos_agreement', $profile->tos_agreement))>
                                    <label class="form-check-label" for="l2">Agree to Terms of Service</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="code_of_conduct_agreement" id="l3" required @checked(old('code_of_conduct_agreement', $profile->code_of_conduct_agreement))>
                                    <label class="form-check-label" for="l3">Agree to Code of Conduct</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="criminal_record_declaration" id="l4" required @checked(old('criminal_record_declaration', $profile->criminal_record_declaration))>
                                    <label class="form-check-label" for="l4">I declare no criminal history</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="driving_violation_declaration" id="l5" required @checked(old('driving_violation_declaration', $profile->driving_violation_declaration))>
                                    <label class="form-check-label" for="l5">I declare no recent major driving violations</label>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <label class="form-label">Traveler Bio (Optional)</label>
                                <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="2">{{ old('bio', $profile->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-outline-primary px-4">
                            <i class="bi bi-save me-2"></i> Save Changes
                        </button>
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                            <i class="bi bi-send-check me-2"></i> Update and Final Submission
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const activeTabInput = document.getElementById('activeTabInput');
        const tabs = document.querySelectorAll('#verificationTabs button');
        
        // Update progress bar
        function updateProgress() {
            const totalSteps = tabs.length;
            let currentStepIdx = 0;
            tabs.forEach((tab, index) => {
                if (tab.classList.contains('active')) {
                    currentStepIdx = index + 1;
                }
            });
            const percent = Math.round((currentStepIdx / totalSteps) * 100);
            const progressBar = document.getElementById('onboardingProgressBar');
            const progressText = document.getElementById('progressText');
            if (progressBar) {
                progressBar.style.width = percent + '%';
                progressBar.setAttribute('aria-valuenow', percent);
            }
            if (progressText) {
                progressText.innerText = percent + '%';
            }
        }

        // Update hidden input when tab changes
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                activeTabInput.value = event.target.getAttribute('href');
                updateProgress();
            });
        });

        // Restore active tab from hidden input or session
        const lastActiveTab = activeTabInput.value;
        if (lastActiveTab) {
            const tabEl = document.querySelector(`#verificationTabs button[href="${lastActiveTab}"]`);
            if (tabEl) {
                bootstrap.Tab.getOrCreateInstance(tabEl).show();
                updateProgress();
            }
        } else {
            updateProgress(); // initial state for first tab
        }

        // Handle "Save and Next" buttons
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', function() {
                const nextTabId = this.getAttribute('data-next');
                activeTabInput.value = nextTabId;
                this.closest('form').submit();
            });
        });

        // Auto-switch tabs if validation failed in a hidden tab
        const firstErrorPath = document.querySelector('.is-invalid');
        if (firstErrorPath) {
            const pane = firstErrorPath.closest('.tab-pane');
            if (pane) {
                const tabLink = document.querySelector(`[href="#${pane.id}"]`);
                if (tabLink) bootstrap.Tab.getOrCreateInstance(tabLink).show();
            }
        }
    });
</script>
@endpush
@endsection
