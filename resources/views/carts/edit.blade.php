@extends('partials.layouts.master')

@section('title', 'Carritos')
@section('sub-title', 'Editar carrito')
@section('pagetitle', 'Editar: ' . $cart->name)

@section('content')

<div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card p-4">

            <form method="POST" action="{{ route('carts.update', $cart) }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nombre del Carrito *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $cart->name) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Apodo / Código *</label>
                    <input type="text" name="code_name" class="form-control" required value="{{ old('code_name', $cart->code_name) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="1" {{ $cart->status ? 'selected' : '' }}>Habilitado</option>
                        <option value="0" {{ !$cart->status ? 'selected' : '' }}>Deshabilitado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('carts.index') }}" class="btn btn-light border">Cancelar</a>

            </form>

        </div>
    </div>
</div>

@endsection
