@extends(backpack_view('blank'))

@section('content')
    {{-- Mount point for Inertia --}}
    <div id="app" data-page="{{ json_encode($page) }}">
        @inertia
    </div>
@endsection

@push('after_scripts')
    @vite('resources/js/app.js')
@endpush
