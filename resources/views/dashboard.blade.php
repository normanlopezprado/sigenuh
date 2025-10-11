@extends('partials.layouts.master3')

@section('title', 'Sistema de gestión nutricional Hospitalaria')
@section('sub-title', 'Inicio')
@section('buttonTitle', 'Share')
@section('link', '#!')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <style>
        .dashboard-row {
            align-items: flex-start;
        }
        .calendarContainer {
            height: auto !important;
            min-height: auto !important;
        }

        @media (min-width: 1200px) {
            /* Layout dependiente del permiso de calendario */
            @php
                $canSeeCalendar = auth()->check() && auth()->user()->can('calendars.index');
            @endphp

            @if ($canSeeCalendar)
                /* Con calendario: 20% calendario + 20% cada card de menú */
                .dashboard-row > .col-calendar { flex: 0 0 20%; max-width: 20%; }
                .dashboard-row > .col-menu     { flex: 0 0 20%; max-width: 20%; }
            @else
                /* Sin calendario: 4 cards por fila (25% cada una) */
                .dashboard-row > .col-menu     { flex: 0 0 25%; max-width: 25%; }
            @endif
        }
    </style>
@endsection

@section('content')
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');

        // Permiso para ver calendario
        $canSeeCalendar = auth()->check() && auth()->user()->can('calendars.index');

        // Próximos 9 días (hoy + 8)
        $fechas = [];
        for ($i = 0; $i < 9; $i++) {
            $fechas[] = Carbon::today()->addDays($i);
        }

        // 1a fila: 4 días, 2a fila: 5 días
        $fechasFila1 = array_slice($fechas, 0, 4);
        $fechasFila2 = array_slice($fechas, 4);
    @endphp

    <div class="row g-4 dashboard-row">
        {{-- Calendario (solo con permiso) --}}
        @if ($canSeeCalendar)
            <div class="col-12 col-calendar">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Calendario de menús</h6>
                    </div>
                    <div class="card-body calendarContainer">
                        @include('calendars.show')
                    </div>
                </div>
            </div>
        @endif

        {{-- Menús próximos - fila 1 --}}
        @foreach ($fechasFila1 as $f)
            <div class="col-12 col-menu">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-1">
                            {{ ucfirst($f->isoFormat('dddd D [de] MMMM YYYY')) }}
                        </h6>
                        <small class="text-muted">Menús del día</small>
                    </div>
                    <div class="card-body">
                        @include('calendars.dia', ['fecha' => $f->toDateString()])
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Menús próximos - fila 2 --}}
    <div class="row g-4 mt-0 dashboard-row">
        @foreach ($fechasFila2 as $f)
            <div class="col-12 col-menu">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-1">
                            {{ ucfirst($f->isoFormat('dddd D [de] MMMM YYYY')) }}
                        </h6>
                        <small class="text-muted">Menús del día</small>
                    </div>
                    <div class="card-body">
                        @include('calendars.dia', ['fecha' => $f->toDateString()])
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/pages/countup.init.js') }}"></script>
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/js/charts/apexcharts-config.init.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards/dashboard-online-course.init.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
