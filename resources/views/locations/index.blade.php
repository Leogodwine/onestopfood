@extends('layouts.dashboard')

@section('content')
{{-- Typography: match layout.blade.php root (Poppins headlines, Roboto body) --}}
<style>
    .locations-page { font-family: var(--font-body); }
    .locations-page h1, .locations-page h2, .locations-page h3, .locations-page h4, .locations-page h5, .locations-page h6,
    .locations-page .card-title { font-family: var(--font-headline); font-weight: 600; letter-spacing: -0.02em; }
    .locations-page p, .locations-page small, .locations-page .fw-semibold, .locations-page .dropdown-item,
    .locations-page .alert, .locations-page label, .locations-page input, .locations-page select, .locations-page textarea { font-family: var(--font-body); }
</style>
<div class="container-fluid px-0 locations-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 headline-font">My Delivery Addresses</h2>
            <p class="text-muted mb-0">Manage your delivery locations</p>
        </div>
        <button type="button" class="btn btn-success" onclick="openAddAddressModal()">
            <i class="bi bi-plus-circle"></i> Add Address
        </button>
    </div>

    @if($locations->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-geo-alt" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">No delivery addresses</h4>
                <p class="text-muted mb-4">Add your first delivery address to get started</p>
                <button type="button" class="btn btn-success btn-lg" onclick="openAddAddressModal()">
                    <i class="bi bi-plus-circle"></i> Add Your First Address
                </button>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($locations as $location)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100 {{ $location->is_primary ? 'border-success border-2' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    @if($location->is_primary)
                                        <span class="badge bg-success mb-2">
                                            <i class="bi bi-star-fill"></i> Primary Address
                                        </span>
                                    @endif
                                    @if($location->label)
                                        <h5 class="mb-2 fw-bold">{{ $location->label }}</h5>
                                    @else
                                        <h5 class="mb-2 fw-bold text-muted">Address</h5>
                                    @endif
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button type="button" class="dropdown-item edit-location-btn" data-edit-url="{{ route('locations.edit', $location) }}?partial=1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                        </li>
                                        @if(!$location->is_primary)
                                            <li>
                                                <form method="POST" action="{{ route('locations.set-primary', $location) }}">
                                                    @csrf
                                                    <button class="dropdown-item" type="submit">
                                                        <i class="bi bi-star"></i> Set as Primary
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('locations.destroy', $location) }}" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-geo-alt-fill text-success me-2 mt-1"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $location->address_line }}</div>
                                        @if($location->city || $location->region)
                                            <div class="text-muted small mt-1">
                                                {{ $location->city }}{{ $location->region ? ', ' . $location->region : '' }}
                                            </div>
                                        @endif
                                        @if($location->country)
                                            <div class="text-muted small">{{ $location->country }}</div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($location->latitude && $location->longitude)
                                    <div class="text-muted small">
                                        <i class="bi bi-geo"></i> 
                                        {{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}
                                    </div>
                                @endif
                            </div>

                            @if($location->is_primary)
                                <div class="alert alert-success mb-0 py-2">
                                    <small>
                                        <i class="bi bi-check-circle"></i> This is your default delivery location
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@include('locations._modal')

<!-- Edit Delivery Address Modal (content loaded via AJAX) -->
<div class="modal fade locations-page" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-3 overflow-hidden">
            <div class="modal-header bg-success text-white border-0 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="editAddressModalLabel">
                        <i class="bi bi-geo-alt-fill me-2"></i> Edit Delivery Address
                    </h5>
                    <p class="mb-0 small opacity-90 mt-1">Update your delivery location information</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3" id="editAddressModalBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-success mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0 small">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editAddressModal');
    var editModalBody = document.getElementById('editAddressModalBody');
    var loadingHtml = '<div class="text-center py-5 text-muted">' +
        '<div class="spinner-border text-success mb-2" role="status"><span class="visually-hidden">Loading...</span></div>' +
        '<p class="mb-0 small">Loading form...</p></div>';

    document.querySelectorAll('.edit-location-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-edit-url');
            if (!url) return;
            editModalBody.innerHTML = loadingHtml;
            var modal = new bootstrap.Modal(editModal);
            modal.show();
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                credentials: 'same-origin'
            })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    editModalBody.innerHTML = html;
                })
                .catch(function() {
                    editModalBody.innerHTML = '<div class="alert alert-danger mb-0">Could not load form. <a href="' + url.replace('?partial=1', '') + '">Open edit page</a>.</div>';
                });
        });
    });

    editModal.addEventListener('hidden.bs.modal', function() {
        editModalBody.innerHTML = loadingHtml;
    });

    // Submit edit form via AJAX and show validation errors in modal
    editModalBody.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form.classList || !form.classList.contains('edit-address-form')) return;
        e.preventDefault();

        var submitBtn = form.querySelector('button[type="submit"]');
        var errorAlert = form.querySelector('#editFormErrorAlert');
        var errorList = form.querySelector('#editFormErrorList');
        var originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';
        }
        if (errorAlert) {
            errorAlert.classList.add('d-none');
            if (errorList) errorList.innerHTML = '';
        }
        form.querySelectorAll('.is-invalid').forEach(function(el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback').forEach(function(el) {
            if (el.id !== 'editFormErrorList') el.remove();
        });

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function(r) {
            if (r.status === 422) return r.json().then(function(data) { return { validation: true, data: data }; });
            if (r.ok || r.redirected) return { success: true };
            return r.text().then(function() { return { success: false }; });
        })
        .then(function(result) {
            if (result.validation && result.data && result.data.errors) {
                var err = result.data.errors;
                if (errorAlert && errorList) {
                    errorAlert.classList.remove('d-none');
                    Object.keys(err).forEach(function(key) {
                        var messages = Array.isArray(err[key]) ? err[key] : [err[key]];
                        messages.forEach(function(msg) {
                            var li = document.createElement('li');
                            li.textContent = msg;
                            errorList.appendChild(li);
                        });
                        var input = form.querySelector('[name="' + key + '"]');
                        if (input) {
                            input.classList.add('is-invalid');
                            var fb = input.parentNode.querySelector('.invalid-feedback:not(#editFormErrorList)');
                            if (!fb) {
                                fb = document.createElement('div');
                                fb.className = 'invalid-feedback';
                                input.parentNode.appendChild(fb);
                            }
                            fb.textContent = messages[0];
                        }
                    });
                }
            } else if (result.success) {
                var modalInstance = bootstrap.Modal.getInstance(editModal);
                if (modalInstance) modalInstance.hide();
                window.location.reload();
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        })
        .catch(function() {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
            if (errorAlert && errorList) {
                errorAlert.classList.remove('d-none');
                errorList.innerHTML = '<li>Something went wrong. Please try again.</li>';
            }
        });
    });
});
</script>
@endsection
