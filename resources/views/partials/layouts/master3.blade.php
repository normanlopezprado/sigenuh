<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'SIGENUH • Sistema de gestión nutricional hospitalaria')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Proyecto de graduación para el hospital regional de occidente">
    <meta property="og:locale" content="es_ES">
    <link rel="shortcut icon" href="{{ asset('assets/images/16650.png') }}">

    {{-- CSS específico de cada vista --}}
    @yield('css')

    {{-- CSS globales --}}
    @include('partials.head-css')
</head>

<body>
    @include('partials.header')
    @include('partials.sidebar')
    @include('partials.preloader')

    <main class="app-wrapper">
        <div class="app-container">
            @include('partials.breadcrumb')

            {{-- Contenido de cada vista --}}
            @yield('content')

            @include('partials.bottom-wrapper')
        </div>
    </main>

    {{-- JS específico por vista (si usas @section('js')) --}}
    @yield('js')

    {{-- JS pusheados desde las vistas con @push('scripts') --}}
    @stack('scripts')
</body>
</html>
