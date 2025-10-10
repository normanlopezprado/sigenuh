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
            .dashboard-row > .col-calendar {
                flex: 0 0 20%;
                max-width: 20%;
            }
            .dashboard-row > .col-menu {
                flex: 0 0 20%;
                max-width: 20%;
            }
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

        
        $fechas = [];
        for ($i = 0; $i < 9; $i++) {
            $fechas[] = Carbon::today()->addDays($i);
        }

        
        $fechasFila1 = array_slice($fechas, 0, 4);
        
        $fechasFila2 = array_slice($fechas, 4);
    @endphp

    
    <div class="row g-4 dashboard-row">
        {{-- Calendario --}}
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
