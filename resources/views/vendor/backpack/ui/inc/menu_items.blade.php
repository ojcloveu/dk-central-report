{{-- Dashboard --}}
<x-backpack::menu-item
    title="{{ trans('backpack::base.dashboard') }}"
    icon="la la-home"
    :link="backpack_url('dashboard')"
/>

{{-- CRUD --}}
<x-backpack::menu-dropdown title="CRUD" icon="las la-list-ul">
    <x-backpack::menu-dropdown-header title="CRUD" />
    <x-backpack::menu-dropdown-item title="Bets" icon="la la-dice" :link="backpack_url('bet')" />
    <x-backpack::menu-dropdown-item title="Channels" icon="las la-tv" :link="backpack_url('channel')" />
    <x-backpack::menu-dropdown-item title="Masters" icon="las la-briefcase" :link="backpack_url('masters')"/>
</x-backpack::menu-dropdown>

{{-- Bet Report --}}
<x-backpack::menu-item title="Bet Report" icon="las la-clipboard" :link="backpack_url('report')" />
<x-backpack::menu-item title="Master Report" icon="las la-clipboard" :link="backpack_url('master')" />

{{-- User Management --}}
<x-backpack::menu-dropdown title="User Management" icon="la la-user">
    <x-backpack::menu-dropdown-header title="User Management" />
    <x-backpack::menu-dropdown-item title="Users" icon="la la-user" :link="backpack_url('user')" />
    <x-backpack::menu-dropdown-item
        title="Roles"
        icon="la la-group"
        :link="backpack_url('role')"
    />
    <x-backpack::menu-dropdown-item
        title="Permissions"
        icon="la la-key"
        :link="backpack_url('permission')"
    />
</x-backpack::menu-dropdown>

{{-- Setting --}}
<x-backpack::menu-item title="Settings" icon="la la-cog" :link="backpack_url('setting')" />

{{-- Dev Toools --}}
<x-backpack::menu-dropdown title="Dev Tools" icon="la la-tools" :withColumns="true">
    <x-theme-tabler::menu-dropdown-column>
        <x-backpack::menu-dropdown-item
            title="Logs"
            icon="la la-terminal"
            :link="backpack_url('log')"
        />
        <x-backpack::menu-dropdown-item
            title="Log Viewers"
            icon="la la-clock"
            :link="backpack_url('log-viewer')"
        />
    </x-theme-tabler::menu-dropdown-column>
</x-backpack::menu-dropdown>
