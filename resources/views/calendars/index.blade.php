@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Calendario')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card mb-0 h-100" style="height: 580px !important;">
                @include('calendars.show')
            </div>
        </div>
    </div>
    @include('partials.social-share-modal')
@endsection

@section('js')

    <!-- Datatable js -->

    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>


    <!-- Datatable init -->
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

@endsection

