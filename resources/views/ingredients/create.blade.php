@extends('partials.layouts.master2')

@section('title', 'sigenhuh')
@section('sub-title', 'Crear Ingrediente' )
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')
@section('modalTarget', 'shareModal')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
@endsection

@section('content')
    <form method="POST" action="{{ route('ingredients.store') }}">
        @csrf
        @include('ingredients.form')
        <button class="btn btn-success">Guardar</button>
        <a href="{{ route('ingredients.index') }}" class="btn btn-secondary">Volver</a>
    </form>
@endsection
@section('js')

    <!-- Air Datepicker js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- Form-layout init -->
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
