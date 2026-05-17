<!-- Add Delivery Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addAddressModalLabel">
                    <i class="bi bi-geo-alt-fill"></i> Add Delivery Address
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAddressForm" method="POST" action="{{ route('locations.store') }}">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-4">Add a new delivery location for your orders</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-bookmark"></i> Label (e.g., Home, Office)
                        </label>
                        <input class="form-control form-control-lg @error('label') is-invalid @enderror" 
                               type="text" 
                               name="label" 
                               id="modal_label"
                               value="{{ old('label') }}" 
                               placeholder="Home">
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-geo-alt"></i> Address Line <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control form-control-lg @error('address_line') is-invalid @enderror" 
                                  name="address_line" 
                                  id="modal_address_line"
                                  rows="3" 
                                  required
                                  placeholder="Enter your full address">{{ old('address_line') }}</textarea>
                        @error('address_line')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-building"></i> City
                            </label>
                            <input class="form-control form-control-lg @error('city') is-invalid @enderror" 
                                   type="text" 
                                   name="city" 
                                   id="modal_city"
                                   value="{{ old('city') }}" 
                                   placeholder="Dar es Salaam">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo"></i> Region
                            </label>
                            <input class="form-control form-control-lg @error('region') is-invalid @enderror" 
                                   type="text" 
                                   name="region" 
                                   id="modal_region"
                                   value="{{ old('region') }}" 
                                   placeholder="Dar es Salaam">
                            @error('region')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-globe"></i> Country
                        </label>
                        <input class="form-control form-control-lg @error('country') is-invalid @enderror" 
                               type="text" 
                               name="country" 
                               id="modal_country"
                               value="{{ old('country', 'Tanzania') }}">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-alt-fill"></i> Latitude (optional)
                            </label>
                            <input class="form-control form-control-lg @error('latitude') is-invalid @enderror" 
                                   type="number" 
                                   step="any" 
                                   name="latitude" 
                                   id="modal_latitude"
                                   value="{{ old('latitude') }}"
                                   placeholder="e.g., -6.7924">
                            <small class="form-text text-muted">For precise location tracking</small>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-alt-fill"></i> Longitude (optional)
                            </label>
                            <input class="form-control form-control-lg @error('longitude') is-invalid @enderror" 
                                   type="number" 
                                   step="any" 
                                   name="longitude" 
                                   id="modal_longitude"
                                   value="{{ old('longitude') }}"
                                   placeholder="e.g., 39.2083">
                            <small class="form-text text-muted">For precise location tracking</small>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_primary" 
                               id="modal_is_primary" 
                               value="1" 
                               {{ old('is_primary') ? 'checked' : '' }}>
                        <label class="form-check-label" for="modal_is_primary">
                            <strong>Set as primary address</strong>
                            <small class="d-block text-muted">This will be your default delivery location</small>
                        </label>
                    </div>

                    <div id="modalErrorAlert" class="alert alert-danger d-none">
                        <ul id="modalErrorList" class="mb-0"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-lg" id="submitAddressBtn">
                        <i class="bi bi-check-circle"></i> Add Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('addAddressModal'));
    const form = document.getElementById('addAddressForm');
    const submitBtn = document.getElementById('submitAddressBtn');
    const errorAlert = document.getElementById('modalErrorAlert');
    const errorList = document.getElementById('modalErrorList');

    // Function to open modal
    window.openAddAddressModal = function() {
        // Reset form
        form.reset();
        document.getElementById('modal_country').value = 'Tanzania';
        errorAlert.classList.add('d-none');
        errorList.innerHTML = '';
        
        // Remove any existing error classes
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        
        modal.show();
    };

    // Handle form submission via AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
        
        // Clear previous errors
        errorAlert.classList.add('d-none');
        errorList.innerHTML = '';
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });

        // Get form data
        const formData = new FormData(form);

        // Submit via AJAX
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    // If status is not ok, return data with success: false
                    if (!response.ok) {
                        return { success: false, ...data };
                    }
                    return data;
                });
            } else {
                // If not JSON, might be a redirect or HTML error
                return response.text().then(text => {
                    throw new Error('Unexpected response format');
                });
            }
        })
        .then(data => {
            if (data.success) {
                // Close modal
                modal.hide();
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Address added successfully!', 'success');
                } else {
                    alert('Address added successfully!');
                }
                
                // Reload page or refresh address list
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                // Handle validation errors
                if (data.errors) {
                    errorAlert.classList.remove('d-none');
                    Object.keys(data.errors).forEach(key => {
                        const errorMessages = Array.isArray(data.errors[key]) ? data.errors[key] : [data.errors[key]];
                        errorMessages.forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                        
                        // Add error class to input
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            // Remove existing feedback if any
                            const existingFeedback = input.parentNode.querySelector('.invalid-feedback');
                            if (existingFeedback) {
                                existingFeedback.remove();
                            }
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = errorMessages[0];
                            input.parentNode.appendChild(feedback);
                        }
                    });
                } else if (data.message) {
                    errorAlert.classList.remove('d-none');
                    const li = document.createElement('li');
                    li.textContent = data.message;
                    errorList.appendChild(li);
                }
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Address';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorAlert.classList.remove('d-none');
            const li = document.createElement('li');
            li.textContent = 'An error occurred. Please try again.';
            errorList.appendChild(li);
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Address';
        });
    });

    // Reset form when modal is closed
    document.getElementById('addAddressModal').addEventListener('hidden.bs.modal', function() {
        form.reset();
        document.getElementById('modal_country').value = 'Tanzania';
        errorAlert.classList.add('d-none');
        errorList.innerHTML = '';
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Address';
    });
});
</script>
