@extends('partials.layouts.master2')

@section('title', 'SIGENUH')
@section('sub-title', 'Accesos -> Usuarios -> Editar usuario')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')
@section('modalTarget', 'shareModal')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
@endsection

@section('content')
    <form method="POST" action="{{ route('usuarios.update',$usuario) }}" enctype="multipart/form-data">

        @csrf
        @method('PUT')
        @include('usuarios.form', ['usuario' => $usuario])
    </form>
@endsection

@section('js')
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
