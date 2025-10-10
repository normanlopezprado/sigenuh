@extends('partials.layouts.master')

@section('title', 'Carritos')
@section('sub-title', 'Crear nuevo carrito')
@section('pagetitle', 'Nuevo carrito')

@section('content')

<div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card p-4">

            <form method="POST" action="{{ route('carts.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nombre del Carrito *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Apodo / Código *</label>
                    <input type="text" name="code_name" class="form-control" required value="{{ old('code_name') }}">
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('carts.index') }}" class="btn btn-light border">Cancelar</a>

            </form>

        </div>
    </div>
</div>

@endsection
