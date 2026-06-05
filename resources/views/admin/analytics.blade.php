@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Analytics & Reports</h2>
    <p class="text-muted mb-0 page-header-subtitle">Key performance indicators for the platform</p>
</div>

<div class="dashboard-card mb-3 mb-md-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Date Range</h5>
    </div>
    <form method="GET" action="{{ route('admin.analytics.index') }}" class="dashboard-filter-form row g-2 align-items-end">
        <div class="col-6 col-md-4 col-lg-3">
            <label class="form-label dashboard-filter-label" for="analytics-from">From</label>
            <input type="date" id="analytics-from" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <label class="form-label dashboard-filter-label" for="analytics-to">To</label>
            <input type="date" id="analytics-to" name="to" value="{{ $to }}" class="form-control">
        </div>
        <div class="col-12 col-md-4 col-lg-3 dashboard-filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="row g-3 g-md-4 mb-3 mb-md-4">
    <div class="col-6 col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-bag-check"></i>
            </div>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value">{{ number_format($completedOrders) }}</div>
            <div class="stat-label">Completed Orders</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-value">{{ number_format($cancelledOrders) }}</div>
            <div class="stat-label">Cancelled Orders</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-currency-exchange"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$revenue, 2) }}</div>
            <div class="stat-label">Revenue (paid)</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-people"></i> Users by Role</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Customers
                    <span class="fw-semibold">{{ number_format($totalCustomers) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Chefs
                    <span class="fw-semibold">{{ number_format($totalChefs) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Travelers
                    <span class="fw-semibold">{{ number_format($totalTravelers) }}</span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="card-title mb-0"><i class="bi bi-truck"></i> Delivery Overview</h5>
                <span class="badge bg-secondary" id="deliveryMapLiveBadge" title="Last data refresh">Live map</span>
            </div>
            <div class="card-body pt-0">
                <p class="mb-2 small text-muted">
                    <strong>Active deliveries:</strong> {{ number_format($activeDeliveries) }}
                    <span class="mx-1">·</span>
                    <span id="deliveryMapStatus">Loading map…</span>
                </p>
                <div id="adminDeliveryMap" class="rounded border" style="height: 320px; min-height: 240px; z-index: 1;"></div>
                <div class="d-flex flex-wrap gap-3 mt-2 small text-muted">
                    <span><span class="d-inline-block rounded-circle me-1 align-middle" style="width:10px;height:10px;background:#28a745;"></span> Live GPS (traveler, last 15 min)</span>
                    <span><span class="d-inline-block rounded-circle me-1 align-middle" style="width:10px;height:10px;background:#e28743;"></span> Delivery address (saved)</span>
                </div>
                <p class="small text-muted mb-0 mt-2">
                    Travelers with an active assignment can share location from <strong>My Deliveries</strong> (browser will ask for permission). This map syncs about once per minute while the tab is visible (not continuously).
                </p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var pollUrl = @json($deliveryMapPollUrl);
    /** Sync interval (ms): not continuous polling; pauses when tab is hidden */
    var SYNC_INTERVAL_MS = 60 * 1000;
    var el = document.getElementById('adminDeliveryMap');
    if (!el || typeof L === 'undefined') return;

    var defaultCenter = [-6.7924, 39.2083];
    var map = L.map('adminDeliveryMap', { scrollWheelZoom: false }).setView(defaultCenter, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var layerGroup = L.layerGroup().addTo(map);
    var statusEl = document.getElementById('deliveryMapStatus');
    var badgeEl = document.getElementById('deliveryMapLiveBadge');

    function fmtTime(iso) {
        if (!iso) return '';
        try {
            var d = new Date(iso);
            return d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        } catch (e) { return ''; }
    }

    function renderMarkers(data) {
        layerGroup.clearLayers();
        var markers = data.markers || [];
        if (markers.length === 0) {
            if (statusEl) {
                statusEl.textContent = 'No pin positions yet. Enable location on traveler devices or ensure orders have delivery coordinates.';
            }
            map.setView(defaultCenter, 12);
            return;
        }

        var bounds = [];
        markers.forEach(function (m) {
            var lat = m.lat, lng = m.lng;
            bounds.push([lat, lng]);
            var color = m.source === 'gps' ? '#28a745' : '#e28743';
            var circle = L.circleMarker([lat, lng], {
                radius: 9,
                color: '#fff',
                weight: 2,
                fillColor: color,
                fillOpacity: 0.9
            });
            var title = 'Order #' + m.order_id + ' · ' + (m.status || '').replace('_', ' ');
            var lines = [
                '<strong>' + title + '</strong>',
                m.traveler ? ('Traveler: ' + escapeHtml(m.traveler)) : '',
                m.source === 'gps' ? ('Live GPS' + (m.location_updated_at ? (' · ' + fmtTime(m.location_updated_at)) : '')) : 'Saved delivery address',
                m.address ? escapeHtml(m.address) : ''
            ].filter(Boolean).join('<br>');
            circle.bindPopup(lines);
            circle.addTo(layerGroup);
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], 14);
        } else {
            map.fitBounds(bounds, { padding: [24, 24], maxZoom: 15 });
        }

        var wc = data.without_coordinates || 0;
        var parts = [markers.length + ' on map'];
        if (wc > 0) parts.push(wc + ' without coordinates');
        if (statusEl) statusEl.textContent = parts.join(' · ');
    }

    function escapeHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function tick() {
        if (document.visibilityState !== 'visible') return;
        fetch(pollUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                renderMarkers(data);
                if (badgeEl && data.generated_at) {
                    badgeEl.textContent = 'Updated ' + fmtTime(data.generated_at);
                    badgeEl.classList.remove('bg-secondary');
                    badgeEl.classList.add('bg-success');
                }
            })
            .catch(function () {
                if (statusEl) statusEl.textContent = 'Could not load live locations.';
                if (badgeEl) {
                    badgeEl.textContent = 'Offline';
                    badgeEl.classList.remove('bg-success');
                    badgeEl.classList.add('bg-danger');
                }
            });
    }

    tick();
    setInterval(tick, SYNC_INTERVAL_MS);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') tick();
    });
})();
</script>
@endpush
@endsection
