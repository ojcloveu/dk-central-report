{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-dropdown title="Dev Tools" icon="las la-clinic-medical" :withColumns="true">
    <x-theme-tabler::menu-dropdown-column>
        <x-backpack::menu-dropdown-item title="Logs" icon='la la-terminal' :link="backpack_url('log')" />
        <x-backpack::menu-dropdown-item title="Log Viewers" icon='la la-clock' :link="backpack_url('log-viewer')" />
    </x-theme-tabler::menu-dropdown-column>
</x-backpack::menu-dropdown>
