@if(!empty($adminPermissions['users.view']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" title="Manage Users">
        <i class="bi bi-people"></i>
        <span class="sidebar-label">{{ __('dashboard.users') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['verifications']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}" href="{{ route('admin.verifications.index') }}" title="Verifications">
        <i class="bi bi-patch-check"></i>
        <span class="sidebar-label">{{ __('dashboard.verifications') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['orders']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}" title="Orders">
        <i class="bi bi-list-check"></i>
        <span class="sidebar-label">{{ __('dashboard.orders') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['finance']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}" href="{{ route('admin.finance.index') }}" title="Finance">
        <i class="bi bi-cash-stack"></i>
        <span class="sidebar-label">{{ __('dashboard.finance') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['invoices']))
<li class="nav-item">
    <a class="nav-link {{ (request()->routeIs('admin.billing.*') || request()->routeIs('admin.invoices.*') || request()->routeIs('invoices.*')) ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}" title="Billing && Invoice">
        <i class="bi bi-receipt"></i>
        <span class="sidebar-label">{{ __('dashboard.billing_invoice') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['logistics']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.logistics.*') ? 'active' : '' }}" href="{{ route('admin.logistics.index') }}" title="{{ __('dashboard.logistics') }}">
        <i class="bi bi-truck"></i>
        <span class="sidebar-label">{{ __('dashboard.logistics') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['disputes']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}" href="{{ route('admin.disputes.index') }}" title="Disputes">
        <i class="bi bi-exclamation-octagon"></i>
        <span class="sidebar-label">{{ __('dashboard.disputes') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['notifications']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}" title="Notifications">
        <i class="bi bi-megaphone"></i>
        <span class="sidebar-label">{{ __('dashboard.notifications') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['analytics']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" href="{{ route('admin.analytics.index') }}" title="Analytics">
        <i class="bi bi-graph-up"></i>
        <span class="sidebar-label">{{ __('dashboard.analytics') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['config']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.config.*') ? 'active' : '' }}" href="{{ route('admin.config.index') }}" title="Configuration">
        <i class="bi bi-gear"></i>
        <span class="sidebar-label">{{ __('dashboard.config') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['zones']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}" href="{{ route('admin.zones.index') }}" title="Zones">
        <i class="bi bi-geo-alt"></i>
        <span class="sidebar-label">{{ __('dashboard.zones') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['system.monitor']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.system.*') ? 'active' : '' }}" href="{{ route('admin.system.index') }}" title="System Monitor">
        <i class="bi bi-speedometer2"></i>
        <span class="sidebar-label">{{ __('dashboard.system_monitor') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['system.security']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }}" href="{{ route('admin.security.index') }}" title="Security">
        <i class="bi bi-shield-lock"></i>
        <span class="sidebar-label">{{ __('dashboard.security') }}</span>
    </a>
</li>
@endif
@if(!empty($adminPermissions['system.backups']))
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}" title="Backups">
        <i class="bi bi-cloud-arrow-down"></i>
        <span class="sidebar-label">{{ __('dashboard.backups') }}</span>
    </a>
</li>
@endif
