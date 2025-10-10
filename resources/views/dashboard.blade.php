
@extends('partials.layouts.master3')

@section('title', 'Sistema de gestión nutricional Hospitalaria')
@section('sub-title', 'Inicio' )
@section('buttonTitle', 'Share')
@section('link', '#!')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">

        <div class="col-md-6 col-xl-4">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h6 class="card-title mb-0">Calendario de menús</h6>

                </div>
                <div class="card-body calendarContainer" style="height: 558px;">
                    @include('calendars.show')
                </div>
            </div>

        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Menú de hoy</h5>
                </div>
                <div class="card-body">
                    @include('calendars.dia', ['fecha' => \Carbon\Carbon::today()->toDateString()])
                    <p class="text-muted fs-13 mt-3">Activity tracking for the entire week, with hours logged each day.</p>

                    <div class="row g-4">
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Total Active Hours</span>
                                <h6 class="mt-1 mb-0">35 hrs</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Active Days</span>
                                <h6 class="mt-1 mb-0">5 Days</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Menú de mañana</h5>
                </div>
                <div class="card-body">
                    @include('calendars.dia',  ['fecha' => \Carbon\Carbon::tomorrow()->toDateString()])
                    <p class="text-muted fs-13 mt-3">Activity tracking for the entire week, with hours logged each day.</p>

                    <div class="row g-4">
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Total Active Hours</span>
                                <h6 class="mt-1 mb-0">35 hrs</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Active Days</span>
                                <h6 class="mt-1 mb-0">5 Days</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 mb-0">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="card-title mb-0">Menú de Pasado Mañana</h5>
                </div>
                <div class="card-body">
                    @include('calendars.dia', ['fecha' => \Carbon\Carbon::today()->addDays(2)->toDateString()])
                    <p class="text-muted fs-13 mt-3">Activity tracking for the entire week, with hours logged each day.</p>
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Total Active Hours</span>
                                <h6 class="mt-1 mb-0">35 hrs</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 rounded bg-light bg-opacity-40">
                                <span class="text-muted fs-12">Active Days</span>
                                <h6 class="mt-1 mb-0">5 Days</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

    <!-- Countup init -->
    <script type="module" src="{{ asset('assets/js/pages/countup.init.js') }}"></script>

    <!-- Swiper init -->
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Air Datepicker js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- ApexChat js -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Online Course Dashboard init -->
    <script src="{{ asset('assets/js/charts/apexcharts-config.init.js') }}"></script>
    <script src="{{ asset('assets/js/dashboards/dashboard-online-course.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
