@extends('partials.layouts.master3')

@section('title', 'Sistema de gestión nutricional Hospitalaria')
@section('sub-title', 'Inicio')
@section('buttonTitle', 'Share')
@section('link', '#!')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <style>
    
    .dashboard-grid{
        display:grid;
        grid-template-columns:1fr; 
        gap:1rem;
        align-items:start;
    }

    .dashboard-grid>.grid-item{
        min-width:0;
        position:relative;
        z-index:0;
    }

    .calendarContainer{
        height:auto !important;
        min-height:auto !important;
        position:relative;
        z-index:1;
    }
    .grid-item-calendar{ z-index:2; }

    .card{
        overflow:visible !important;
        padding:clamp(.75rem, 1.2vw + .25rem, 1rem);
        transition:all .3s ease;
    }
    .card-title{
        font-size:clamp(1.1rem, 1.2vw + .6rem, 1.25rem);
        line-height:1.25;
    }
    .card-body{
        font-size:clamp(.95rem, .6vw + .65rem, 1rem);
        padding-top:clamp(.6rem, 1vw + .2rem, .75rem);
        line-height:1.5;
    }
    small.text-muted{

        font-size:clamp(.82rem, .5vw + .55rem, .95rem);
    }

    .air-datepicker-global-container{ z-index:2000 !important; }

    @media (min-width: 768px){
        .dashboard-grid{ grid-template-columns:repeat(2, 1fr); }
    }

    @media (min-width: 1200px){
        .dashboard-grid{ grid-template-columns:repeat(3, 1fr); }
        .card-title{ font-size:clamp(1.15rem, .9vw + .7rem, 1.35rem); }
        .card-body{ font-size:clamp(1rem, .4vw + .8rem, 1.06rem); }
    }

      
    @media (min-width: 2560px) and (max-width: 3839.98px){
        .dashboard-grid{ grid-template-columns:repeat(4, 1fr); }

        .card{ padding:1.4rem; }
        .card-title{ font-size:1.45rem; }  
        .card-body{ font-size:1.08rem; }   
        small.text-muted{ font-size:1rem; }
    }

      
    @media (min-width: 3840px){
        .dashboard-grid{ grid-template-columns:repeat(4, 1fr); }

        .card{ padding:2rem; }
        .card-title{ font-size:2rem; }     
        .card-body{ font-size:1.7rem; }    
        small.text-muted{ font-size:1.5rem; } 
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
    @endphp

    {{-- ÚNICO GRID: calendario --}}
    <div class="dashboard-grid">
        @if ($canSeeCalendar)
            <div class="grid-item grid-item-calendar">
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

        @foreach ($fechas as $f)
            <div class="grid-item">
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
