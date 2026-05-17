@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>System Configuration</h2>
    <p class="text-muted mb-0">Global runtime settings for the platform</p>
</div>

<form method="POST" action="{{ route('admin.config.update') }}">
    @csrf
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Settings</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Label</th>
                        <th>Value</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $setting)
                        <tr>
                            <td class="small text-monospace">{{ $setting->key }}</td>
                            <td>{{ $setting->label }}</td>
                            <td style="width: 40%;">
                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control form-control-sm">
                                @if($setting->description)
                                    <small class="text-muted">{{ $setting->description }}</small>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $setting->group ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No settings defined yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Save Changes
            </button>
        </div>
    </div>
</form>
@endsection

