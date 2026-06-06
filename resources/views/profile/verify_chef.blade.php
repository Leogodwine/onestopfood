@extends('layouts.dashboard')

@section('content')
<div class="page-header mb-4">
    <h2 class="fw-bold">Chef Verification & Onboarding</h2>
    <p class="text-muted">Please complete all required sections to verify your professional chef account.</p>
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
                        <i class="bi bi-geo-alt me-2"></i> 2. Kitchen Location
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="experience-tab" data-bs-toggle="list" href="#tab-experience" role="tab">
                        <i class="bi bi-award me-2"></i> 3. Experience & Skills
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="documents-tab" data-bs-toggle="list" href="#tab-documents" role="tab">
                        <i class="bi bi-file-earmark-text me-2"></i> 4. Legal Documents
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="banking-tab" data-bs-toggle="list" href="#tab-banking" role="tab">
                        <i class="bi bi-bank me-2"></i> 5. Payout Details
                    </button>
                    <button class="list-group-item list-group-item-action py-3" id="legal-tab" data-bs-toggle="list" href="#tab-legal" role="tab">
                        <i class="bi bi-shield-check me-2"></i> 6. Compliance
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
                                <small class="text-muted">Extracted automatically from your account.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $profile->dob?->format('Y-m-d')) }}" max="{{ date('Y-m-d', strtotime('-18 years')) }}" required>
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIDA ID / Passport Number</label>
                                <input type="text" name="nida_id" class="form-control @error('nida_id') is-invalid @enderror" value="{{ old('nida_id', $profile->nida_id ?? $profile->passport_no) }}" required>
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
                                <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}">
                            </div>
                            <div class="col-md-6">
                                @include('partials.phone-input', [
                                    'label' => 'Contact Phone',
                                    'countryCodeName' => 'emergency_contact_phone_country_code',
                                    'numberName' => 'emergency_contact_phone_number',
                                    'inputId' => 'emergency_contact_phone_number',
                                    'selectId' => 'emergency_contact_phone_country_code',
                                    'value' => old('emergency_contact_phone', $profile->emergency_contact_phone),
                                    'required' => false,
                                ])
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relationship</label>
                                <input type="text" name="emergency_contact_relationship" class="form-control" value="{{ old('emergency_contact_relationship', $profile->emergency_contact_relationship) }}">
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

                <!-- 2. Address -->
                <div class="tab-pane fade" id="tab-address" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Work Address (Kitchen Location)</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Street Address</label>
                                <input type="text" name="street_address" class="form-control @error('street_address') is-invalid @enderror" value="{{ old('street_address', $profile->street_address) }}" required placeholder="e.g. 123 Mbagala Road">
                                @error('street_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @include('profile.partials.city-district-select', ['profile' => $profile])
                            <div class="col-md-6">
                                <label class="form-label">Ward/Neighborhood</label>
                                <input type="text" name="ward_neighborhood" class="form-control" value="{{ old('ward_neighborhood', $profile->ward_neighborhood) }}" required placeholder="e.g. Masaki">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Landmark / Directions</label>
                                <textarea name="landmark_directions" class="form-control" rows="2" required placeholder="e.g. Near the big Baobab tree">{{ old('landmark_directions', $profile->landmark_directions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kitchen Type</label>
                                <select name="kitchen_type" class="form-select" required>
                                    <option value="">Select...</option>
                                    <option value="Home" @selected(old('kitchen_type', $profile->kitchen_type) === 'Home')>Home Kitchen</option>
                                    <option value="Commercial" @selected(old('kitchen_type', $profile->kitchen_type) === 'Commercial')>Commercial Kitchen</option>
                                    <option value="Shared" @selected(old('kitchen_type', $profile->kitchen_type) === 'Shared')>Shared Kitchen</option>
                                    <option value="Restaurant" @selected(old('kitchen_type', $profile->kitchen_type) === 'Restaurant')>Restaurant</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Proof of Kitchen (license, lease, or registration)</label>
                                <input type="file" name="proof_of_kitchen" class="form-control">
                                @if($profile->proof_of_kitchen_path)
                                    <small class="text-success mt-1 d-block"><i class="bi bi-check-circle"></i> Proof of kitchen uploaded</small>
                                @endif
                            </div>
                            <div class="col-12">
                                @include('profile.partials.kitchen-photos-upload', ['profile' => $profile, 'user' => $user])
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-experience">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Experience -->
                <div class="tab-pane fade" id="tab-experience" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Experience and Qualifications</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Years of Experience</label>
                                <select name="years_experience" class="form-select" required>
                                    <option value="<1" @selected(old('years_experience', $profile->years_experience) === '<1')>Less than 1 year</option>
                                    <option value="1-3" @selected(old('years_experience', $profile->years_experience) === '1-3')>1–3 years</option>
                                    <option value="3-5" @selected(old('years_experience', $profile->years_experience) === '3-5')>3–5 years</option>
                                    <option value="5+" @selected(old('years_experience', $profile->years_experience) === '5+')>5+ years</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Professional Training (Optional)</label>
                                <input type="file" name="professional_training" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Food Safety Certificate</label>
                                <input type="file" name="food_safety_cert" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Previous Employment / Business Info</label>
                                <textarea name="prev_employment" class="form-control" rows="2">{{ old('prev_employment', $profile->prev_employment) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Gap Explanation (If inactive for >6 months)</label>
                                <textarea name="gap_explanation" class="form-control" rows="2">{{ old('gap_explanation', $profile->gap_explanation) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Bio (Profile Description - Min 50 characters)</label>
                                <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="3" minlength="50" required placeholder="Tell customers about your culinary journey...">{{ old('bio', $profile->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Estimated Preparation Time</label>
                                <input type="text" name="estimated_prep_time" class="form-control" placeholder="e.g. 30-45 minutes" value="{{ old('estimated_prep_time', $profile->estimated_prep_time) }}">
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-outline-primary px-4">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-primary px-4 btn-next" data-next="#tab-documents">
                                Save and Next <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. Legal Documents -->
                <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Business and Legal Documents</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Business License (Optional but prioritized)</label>
                                <input type="file" name="business_license" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Food Handling Permit</label>
                                <input type="file" name="food_handling_permit" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax Identification Number (TIN)</label>
                                <input type="text" name="tin_number" class="form-control" value="{{ old('tin_number', $profile->tin_number) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Health Inspection Certificate</label>
                                <input type="file" name="health_inspection_cert" class="form-control">
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

                <!-- 5. Banking -->
                <div class="tab-pane fade" id="tab-banking" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Banking and Payment Details</h5>
                        <p class="text-muted small mb-4">Used for receiving your earnings from the platform.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile->bank_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $profile->account_holder_name ?? $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $profile->account_number) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Money Number (Optional)</label>
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

                <!-- 6. Legal -->
                <div class="tab-pane fade" id="tab-legal" role="tabpanel">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="fw-bold mb-4">Legal and Compliance</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="background_check_consent" id="check1" required @checked(old('background_check_consent', $profile->background_check_consent))>
                                    <label class="form-check-label" for="check1">
                                        I consent to a professional background check.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="tos_agreement" id="check2" required @checked(old('tos_agreement', $profile->tos_agreement))>
                                    <label class="form-check-label" for="check2">
                                        I agree to the <a href="#">Terms of Service</a>.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="code_of_conduct_agreement" id="check3" required @checked(old('code_of_conduct_agreement', $profile->code_of_conduct_agreement))>
                                    <label class="form-check-label" for="check3">
                                        I agree to the Professional Code of Conduct.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="criminal_record_declaration" id="check4" required @checked(old('criminal_record_declaration', $profile->criminal_record_declaration))>
                                    <label class="form-check-label" for="check4">
                                        I declare that I have no criminal record related to food safety or public safety.
                                    </label>
                                </div>
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

@endsection

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
