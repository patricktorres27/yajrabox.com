<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.meta')

    <!-- Fonts -->
    <link rel="stylesheet" href="https://use.typekit.net/ins2wgm.css">

    <!-- Scripts -->
    @vite(['resources/css/docs.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles

    @php
        $routesThatAreAlwaysLightMode = collect([
            'marketing',
            'team',
        ])
    @endphp

    <script>
        const alwaysLightMode = {{ ($routesThatAreAlwaysLightMode->contains(request()->route()->getName())) ? 'true' : 'false' }};
    </script>

    @include('partials.theme')
</head>
<body
        x-data="{
        navIsOpen: false,
        searchIsOpen: false,
        search: '',
    }"
        class="language-php h-full w-full font-sans text-gray-900 antialiased"
>

@yield('content')

@include('partials.footer')

<x-search-modal/>

<script>
    var algolia_app_id = '{{ config('algolia.connections.main.id', false) }}';
    var algolia_search_key = '{{ config('algolia.connections.main.search_key', false) }}';
    var version = '{{ $currentVersion ?? DEFAULT_VERSION }}';
    var package = '{{ $package ?? DEFAULT_PACKAGE }}';
</script>

@include('partials.analytics')
</body>
</html>
