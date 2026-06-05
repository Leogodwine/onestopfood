@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">System Monitoring &amp; Maintenance</h2>
    <p class="text-muted mb-0 page-header-subtitle">Monitor platform performance, server resources, error logs, and maintenance tasks</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-hdd-network"></i></div>
            <div class="stat-value">{{ $overview['database_connected'] ? 'Online' : 'Offline' }}</div>
            <div class="stat-label">Database</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-memory"></i></div>
            <div class="stat-value">{{ $overview['memory_usage_mb'] }} MB</div>
            <div class="stat-label">Memory Usage</div>
            <div class="stat-change text-muted small mt-2">Peak: {{ $overview['memory_peak_mb'] }} MB</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="bi bi-device-hdd"></i></div>
            <div class="stat-value">
                @if($overview['disk_used_percent'] !== null)
                    {{ $overview['disk_used_percent'] }}%
                @else
                    N/A
                @endif
            </div>
            <div class="stat-label">Disk Used</div>
            @if($overview['disk_free_gb'] !== null)
                <div class="stat-change text-muted small mt-2">{{ $overview['disk_free_gb'] }} GB free</div>
            @endif
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card {{ $overview['maintenance_mode'] ? 'stat-blue' : 'stat-green' }}">
            <div class="stat-icon"><i class="bi bi-tools"></i></div>
            <div class="stat-value">{{ $overview['maintenance_mode'] ? 'On' : 'Off' }}</div>
            <div class="stat-label">Maintenance Mode</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="dashboard-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Environment</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><th style="width: 40%;">Application</th><td>{{ $overview['app_name'] }}</td></tr>
                        <tr><th>Environment</th><td><span class="badge bg-{{ $overview['app_env'] === 'production' ? 'success' : 'secondary' }}">{{ $overview['app_env'] }}</span></td></tr>
                        <tr><th>Laravel</th><td>{{ $overview['laravel_version'] }}</td></tr>
                        <tr><th>PHP</th><td>{{ $overview['php_version'] }}</td></tr>
                        <tr><th>Debug Mode</th><td>{{ $overview['debug_mode'] ? 'Enabled' : 'Disabled' }}</td></tr>
                        <tr><th>Cache Driver</th><td>{{ $overview['cache_driver'] }}</td></tr>
                        <tr><th>Queue Driver</th><td>{{ $overview['queue_driver'] }}</td></tr>
                        <tr><th>Session Driver</th><td>{{ $overview['session_driver'] }}</td></tr>
                        <tr><th>Log File Size</th><td>{{ number_format($overview['log_size_bytes'] / 1024, 1) }} KB</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="dashboard-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-wrench"></i> Maintenance Tasks</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Run safe maintenance commands after updates or configuration changes.</p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @foreach(['cache' => 'Clear Cache', 'config' => 'Clear Config', 'views' => 'Clear Views', 'routes' => 'Clear Routes', 'optimize' => 'Optimize Clear'] as $task => $label)
                        <form method="POST" action="{{ route('admin.system.tasks') }}">
                            @csrf
                            <input type="hidden" name="task" value="{{ $task }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>

                <hr>

                <h6 class="mb-3">Maintenance Mode</h6>
                @if($overview['maintenance_mode'])
                    <form method="POST" action="{{ route('admin.system.maintenance') }}" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="action" value="disable">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-play-circle"></i> Disable Maintenance Mode
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.system.maintenance') }}">
                        @csrf
                        <input type="hidden" name="action" value="enable">
                        <div class="mb-2">
                            <label class="form-label small">Optional message for visitors</label>
                            <input type="text" name="message" class="form-control form-control-sm" maxlength="500" placeholder="We are performing scheduled maintenance...">
                        </div>
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Enable maintenance mode? Visitors will not be able to use the site.')">
                            <i class="bi bi-pause-circle"></i> Enable Maintenance Mode
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="bi bi-journal-text"></i> Recent Error Log</h5>
        <span class="badge bg-secondary">Last 150 lines</span>
    </div>
    <div class="card-body p-0">
        <pre class="mb-0 p-3 bg-dark text-light small" style="max-height: 420px; overflow: auto; white-space: pre-wrap;">@foreach($logLines as $line){{ $line }}
@endforeach</pre>
    </div>
</div>
@endsection
