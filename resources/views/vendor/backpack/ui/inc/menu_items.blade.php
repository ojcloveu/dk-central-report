{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title='Settings' icon='la la-cog' :link="backpack_url('setting')" />

<x-backpack::menu-dropdown title="Dev Tools" icon="la la-tools" :withColumns="true">
    <x-theme-tabler::menu-dropdown-column>
        <x-backpack::menu-dropdown-item title="Logs" icon='la la-terminal' :link="backpack_url('log')" />
        <x-backpack::menu-dropdown-item title="Log Viewers" icon='la la-clock' :link="backpack_url('log-viewer')" />
    </x-theme-tabler::menu-dropdown-column>
</x-backpack::menu-dropdown>

<x-backpack::menu-item title="Bets" icon="la la-question" :link="backpack_url('bet')" />
<x-backpack::menu-item title="Report" icon="la la-question" :link="backpack_url('report')" />