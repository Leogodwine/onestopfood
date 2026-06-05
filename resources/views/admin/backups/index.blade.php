@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Backup &amp; Recovery</h2>
        <div class="page-header-actions">
            <form method="POST" action="{{ route('admin.backups.store') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success page-header-action-btn" onclick="return confirm('Create a new database backup now?')">
                    <i class="bi bi-cloud-download"></i> Backup
                </button>
            </form>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Create database backups, schedule automatic backups, restore data, and monitor backup status</p>
</div>

@if($errors->has('backup'))
    <div class="alert alert-danger">{{ $errors->first('backup') }}</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="stat-value">{{ ucfirst($scheduleFrequency) }}</div>
            <div class="stat-label">Schedule</div>
            <div class="stat-change small mt-2 {{ $autoEnabled ? 'text-success' : 'text-muted' }}">
                {{ $autoEnabled ? 'Automatic backups enabled' : 'Automatic backups disabled' }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-archive"></i></div>
            <div class="stat-value">{{ $backups->total() }}</div>
            <div class="stat-label">Total Backups</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="stat-value">{{ $retentionDays }} days</div>
            <div class="stat-label">Retention Policy</div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-calendar2-week"></i> Automatic Backup Schedule</h5>
    </div>
    <form method="POST" action="{{ route('admin.backups.schedule') }}">
        @csrf
        <div class="card-body row g-3">
            @foreach($backupSettings as $setting)
                <div class="col-md-4">
                    <label class="form-label">{{ $setting->label }}</label>
                    @if($setting->key === 'backup_auto_enabled')
                        <select name="settings[{{ $setting->key }}]" class="form-select">
                            <option value="true" @selected($setting->value === 'true' || $setting->value === '1')>Enabled</option>
                            <option value="false" @selected($setting->value === 'false' || $setting->value === '0')>Disabled</option>
                        </select>
                    @elseif($setting->key === 'backup_schedule_frequency')
                        <select name="settings[{{ $setting->key }}]" class="form-select">
                            <option value="daily" @selected($setting->value === 'daily')>Daily (2:00 AM server time)</option>
                            <option value="weekly" @selected($setting->value === 'weekly')>Weekly</option>
                        </select>
                    @else
                        <input type="number" min="1" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control">
                    @endif
                    @if($setting->description)
                        <small class="text-muted">{{ $setting->description }}</small>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="card-footer bg-light text-end">
            <button type="submit" class="btn btn-primary btn-sm">Save Schedule</button>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-database-check"></i> Backup History</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Created By</th>
                    <th>Completed</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups as $backup)
                    <tr>
                        <td class="small font-monospace">{{ $backup->filename }}</td>
                        <td>{{ $backup->humanSize() }}</td>
                        <td>
                            <span class="badge bg-{{ $backup->status === 'completed' ? 'success' : ($backup->status === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst($backup->status) }}
                            </span>
                            @if($backup->error_message)
                                <div class="small text-danger">{{ Str::limit($backup->error_message, 60) }}</div>
                            @endif
                        </td>
                        <td>{{ $backup->automatic ? 'Automatic' : 'Manual' }}</td>
                        <td>{{ $backup->creator?->name ?? 'System' }}</td>
                        <td class="small">{{ $backup->completed_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                @if($backup->status === 'completed')
                                    <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#restoreModal{{ $backup->id }}">Restore</button>
                                @endif
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" onsubmit="return confirm('Delete this backup permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No backups yet. Create your first backup above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($backups->hasPages())
        <div class="card-footer">{{ $backups->links() }}</div>
    @endif
</div>

@foreach($backups as $backup)
    @if($backup->status === 'completed')
        <div class="modal fade" id="restoreModal{{ $backup->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.backups.restore', $backup) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Restore Database</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                This will overwrite the current database with <strong>{{ $backup->filename }}</strong>. This action cannot be undone.
                            </div>
                            <label class="form-label">Type <code>RESTORE</code> to confirm</label>
                            <input type="text" name="confirm" class="form-control" required autocomplete="off">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Restore Backup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
