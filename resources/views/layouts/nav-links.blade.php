@if (auth()->user()->hasAdminAccess())
    <div class="space-y-1 pt-4 border-t border-gray-100">
        <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate sidebar-text">
            Administration
        </p>
        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ Route::is('admin.dashboard') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Admin Panel</span>
        </a>
        <a href="{{ route('admin.parking-locations.index') }}"
           class="sidebar-link {{ Route::is('admin.parking-locations.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Parking Locations</span>
        </a>
        <a href="{{ route('admin.parking-slots.index') }}"
           class="sidebar-link {{ Route::is('admin.parking-slots.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Parking Slots</span>
        </a>
        <a href="{{ route('admin.reservations.index') }}"
           class="sidebar-link {{ Route::is('admin.reservations.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Reservations</span>
        </a>
        <a href="{{ route('admin.subscriptions.index') }}"
           class="sidebar-link {{ Route::is('admin.subscriptions.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Subscriptions</span>
        </a>
    </div>

@elseif (auth()->user()->hasOperatorAccess())
    <div class="space-y-1">
        <p class="px-3 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate sidebar-text">
            Operator
        </p>
        <a href="{{ route('staff.dashboard') }}"
           class="sidebar-link {{ Route::is('staff.dashboard') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Dashboard</span>
        </a>
        <a href="{{ route('staff.scan.page') }}"
           class="sidebar-link {{ Route::is('staff.scan.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Scan</span>
        </a>
        <a href="{{ route('staff.payments.index') }}"
           class="sidebar-link {{ Route::is('staff.payments.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Payments</span>
        </a>
    </div>

@else
    <div class="space-y-1">
        <a href="{{ route('home') }}"
           class="sidebar-link {{ Route::is('home') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Dashboard</span>
        </a>
        <a href="{{ route('parking.index') }}"
           class="sidebar-link {{ Route::is('parking.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Parking</span>
        </a>
        <a href="{{ route('vehicles.index') }}"
           class="sidebar-link {{ Route::is('vehicles.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">My Vehicles</span>
        </a>
        <a href="{{ route('reservations.index') }}"
           class="sidebar-link {{ Route::is('reservations.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Reservations</span>
        </a>
        <a href="{{ route('subscription.index') }}"
           class="sidebar-link {{ Route::is('subscription.*') ? 'bg-emerald-200 text-gray-900' : '' }}">
            <span class="sidebar-text truncate">Subscription</span>
        </a>
    </div>
@endif