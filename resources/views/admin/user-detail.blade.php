@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">User Details: {{ $user->name }}</h2>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-outline-primary page-header-action-btn" href="{{ route('admin.users.index') }}">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Review user information and documents</p>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <!-- User Information -->
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-person"></i> User Information</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td class="fw-semibold" style="width: 200px;">Name:</td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        @if($user->avatar_url)
                            <tr>
                                <td class="fw-semibold">Profile picture:</td>
                                <td>
                                    <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="fw-semibold">Email:</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        @if($user->phone)
                            <tr>
                                <td class="fw-semibold">Phone:</td>
                                <td>{{ $user->phone }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="fw-semibold">Role:</td>
                            <td>
                                <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                                @if($user->role === 'admin')
                                    <span class="ms-2">@include('admin.partials.role-badge', ['user' => $user])</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Status:</td>
                            <td>
                                <span class="badge badge-{{ $user->status === 'approved' ? 'success' : ($user->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">User ID:</td>
                            <td>#{{ $user->id }}</td>
                        </tr>
                        @if($user->last_login_at)
                            <tr>
                                <td class="fw-semibold">{{ __('auth.last_sign_in') }}:</td>
                                <td>
                                    {{ $user->last_login_at->format('F d, Y h:i A') }}
                                    @if($user->last_login_ip)
                                        <br><small class="text-muted">IP: {{ $user->last_login_ip }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="fw-semibold">Registered:</td>
                            <td>{{ $user->created_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        @if($user->approved_at)
                            <tr>
                                <td class="fw-semibold">Approved:</td>
                                <td>{{ $user->approved_at->format('F d, Y h:i A') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chef Profile -->
        @if($user->chefProfile)
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-egg-fried"></i> Chef Profile</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @if($user->chefProfile->bio)
                                <tr>
                                    <td class="fw-semibold" style="width: 200px;">Bio:</td>
                                    <td>{{ $user->chefProfile->bio }}</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->dob)
                                <tr>
                                    <td class="fw-semibold">Date of Birth:</td>
                                    <td>{{ $user->chefProfile->dob->format('M d, Y') }}</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->nida_id || $user->chefProfile->passport_no)
                                <tr>
                                    <td class="fw-semibold">ID / Passport:</td>
                                    <td>{{ $user->chefProfile->nida_id ?: $user->chefProfile->passport_no }}</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->nationality)
                                <tr>
                                    <td class="fw-semibold">Nationality:</td>
                                    <td>{{ $user->chefProfile->nationality }}</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->gender)
                                <tr>
                                    <td class="fw-semibold">Gender:</td>
                                    <td>{{ ucfirst($user->chefProfile->gender) }}</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->emergency_contact_name)
                                <tr>
                                    <td class="fw-semibold">Emergency Contact:</td>
                                    <td>{{ $user->chefProfile->emergency_contact_name }} ({{ $user->chefProfile->emergency_contact_phone }})</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->cuisine_type)
                                <tr>
                                    <td class="fw-semibold">Cuisine Type:</td>
                                    <td>{{ $user->chefProfile->cuisine_type }}</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->years_experience)
                                <tr>
                                    <td class="fw-semibold">Experience:</td>
                                    <td>{{ $user->chefProfile->years_experience }} years</td>
                                </tr>
                            @endif
                            @if($user->chefProfile->kitchen_address || $user->chefProfile->street_address)
                                <tr>
                                    <td class="fw-semibold">Kitchen Address:</td>
                                    <td>
                                        {{ $user->chefProfile->street_address ?: $user->chefProfile->kitchen_address }}<br>
                                        {{ $user->chefProfile->ward_neighborhood }}, {{ $user->chefProfile->city_district }}
                                    </td>
                                </tr>
                            @endif
                            @if($user->chefProfile->bank_name)
                                <tr>
                                    <td class="fw-semibold">Banking:</td>
                                    <td>
                                        <strong>{{ $user->chefProfile->bank_name }}</strong><br>
                                        {{ $user->chefProfile->account_number }} ({{ $user->chefProfile->account_holder_name }})
                                    </td>
                                </tr>
                            @endif
                            @if($user->chefProfile->selfie_path)
                                <tr>
                                    <td class="fw-semibold">Selfie:</td>
                                    <td>
                                        @if($selfieUrl = \App\Support\UploadedDocumentUrl::profile($user, 'selfie'))
                                            <a href="{{ $selfieUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">View Selfie</a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        @endif

        <!-- Traveler Profile -->
        @if($user->travelerProfile)
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-truck"></i> Traveler Profile</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @if($user->travelerProfile->dob)
                                <tr>
                                    <td class="fw-semibold">Date of Birth:</td>
                                    <td>{{ $user->travelerProfile->dob->format('M d, Y') }}</td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->nida_id || $user->travelerProfile->driving_license_no)
                                <tr>
                                    <td class="fw-semibold">ID / License:</td>
                                    <td>{{ $user->travelerProfile->nida_id ?: $user->travelerProfile->driving_license_no }}</td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->nationality)
                                <tr>
                                    <td class="fw-semibold">Nationality:</td>
                                    <td>{{ $user->travelerProfile->nationality }}</td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->gender)
                                <tr>
                                    <td class="fw-semibold">Gender:</td>
                                    <td>{{ ucfirst($user->travelerProfile->gender) }}</td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->vehicle_type)
                                <tr>
                                    <td class="fw-semibold" style="width: 200px;">Vehicle:</td>
                                    <td>{{ ucfirst($user->travelerProfile->vehicle_type) }} - {{ $user->travelerProfile->vehicle_make }} {{ $user->travelerProfile->vehicle_model }} ({{ $user->travelerProfile->vehicle_reg_no }})</td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->license_number)
                                <tr>
                                    <td class="fw-semibold">License:</td>
                                    <td>{{ $user->travelerProfile->license_number }} (Class: {{ $user->travelerProfile->license_class }})<br>Exp: {{ $user->travelerProfile->license_expiry_date?->format('M d, Y') }}</td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->bank_name)
                                <tr>
                                    <td class="fw-semibold">Banking:</td>
                                    <td>
                                        <strong>{{ $user->travelerProfile->bank_name }}</strong><br>
                                        {{ $user->travelerProfile->account_number }} ({{ $user->travelerProfile->account_holder_name }})
                                    </td>
                                </tr>
                            @endif
                            @if($user->travelerProfile->selfie_path)
                                <tr>
                                    <td class="fw-semibold">Selfie:</td>
                                    <td>
                                        @if($selfieUrl = \App\Support\UploadedDocumentUrl::profile($user, 'selfie'))
                                            <a href="{{ $selfieUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">View Selfie</a>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        @endif

        <!-- Locations -->
        @if($user->locations->isNotEmpty())
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-geo-alt"></i> Locations</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Primary</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->locations as $location)
                                <tr>
                                    <td>{{ $location->label }}</td>
                                    <td>{{ $location->address_line }}</td>
                                    <td>{{ $location->city }}</td>
                                    <td>
                                        @if($location->is_primary)
                                            <span class="badge badge-success">Primary</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Verification Documents -->
        @if($documents->isNotEmpty())
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-file-earmark-check"></i> Verification Documents</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Document No</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th>Uploaded</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</td>
                                    <td>{{ $doc->document_no ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($doc->expires_at)
                                            @php
                                                $daysLeft = now()->diffInDays($doc->expires_at, false);
                                            @endphp
                                            <span class="small {{ $daysLeft <= 30 ? 'text-danger' : 'text-muted' }}">
                                                {{ $doc->expires_at->format('M d, Y') }}
                                                @if($daysLeft <= 30)
                                                    <br><strong>Expiring soon</strong>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($doc->url())
                                            <a href="{{ $doc->url() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">View</a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-gear"></i> Actions</h5>
            </div>
            <div class="d-grid gap-2">
                @if($user->status === 'pending' && !empty($adminPermissions['users.approve']))
                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                        @csrf
                        <button class="btn btn-success w-100" type="submit">
                            <i class="bi bi-check-circle"></i> Approve User
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.reject', $user) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Rejection reason (optional)</label>
                            <textarea class="form-control form-control-sm" name="reason" rows="3" placeholder="Reason for rejection..."></textarea>
                        </div>
                        <button class="btn btn-danger w-100" type="submit">
                            <i class="bi bi-x-circle"></i> Reject User
                        </button>
                    </form>
                @elseif($user->status === 'approved' && !empty($adminPermissions['users.manage']))
                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                        @csrf
                        <button class="btn btn-warning w-100" type="submit">
                            <i class="bi bi-pause-circle"></i> Suspend User
                        </button>
                    </form>
                @elseif($user->status === 'suspended' && !empty($adminPermissions['users.approve']))
                    <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">
                        @csrf
                        <button class="btn btn-success w-100" type="submit">
                            <i class="bi bi-play-circle"></i> Unsuspend User
                        </button>
                    </form>
                @endif
                @if(!empty($adminPermissions['users.impersonate']) && auth()->id() !== $user->id)
                    <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                        @csrf
                        <button class="btn btn-outline-primary w-100" type="submit">
                            <i class="bi bi-person-badge"></i> Impersonate User (read-only)
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- User Statistics -->
        <div class="dashboard-card mt-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-bar-chart"></i> Statistics</h5>
            </div>
            <div class="p-3">
                @if($user->role === 'chef')
                    <div class="mb-3">
                        <div class="text-muted small">Total Meals</div>
                        <div class="h4 mb-0">{{ $user->meals()->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Total Orders</div>
                        <div class="h4 mb-0">{{ $user->ordersAsChef()->count() }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Average Rating</div>
                        <div class="h4 mb-0">
                            @if($user->average_rating > 0)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill"></i> {{ $user->average_rating }}
                                </span>
                            @else
                                <span class="text-muted">No ratings yet</span>
                            @endif
                        </div>
                    </div>
                @elseif($user->role === 'traveler')
                    <div class="mb-3">
                        <div class="text-muted small">Total Deliveries</div>
                        <div class="h4 mb-0">{{ $user->deliveries()->count() }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Completed Deliveries</div>
                        <div class="h4 mb-0">{{ $user->deliveries()->where('status', 'delivered')->count() }}</div>
                    </div>
                @elseif($user->role === 'customer')
                    <div class="mb-3">
                        <div class="text-muted small">Total Orders</div>
                        <div class="h4 mb-0">{{ $user->ordersAsCustomer()->count() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
