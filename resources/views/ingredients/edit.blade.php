@extends('partials.layouts.master2')

@section('title', 'sigenhuh')
@section('sub-title', 'Editar Ingrediente' )
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')
@section('modalTarget', 'shareModal')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
@endsection

@section('content')
    @include('ingredients.form', [
        'action'     => route('ingredients.update', $ingredient),
        'method'     => 'PUT',
        'btnText'    => 'Actualizar',
        'cancelUrl'  => route('ingredients.index'),
        'ingredient' => $ingredient,
    ])
@endsection

@section('js')
    <!-- Air Datepicker js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- Form-layout init -->
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
