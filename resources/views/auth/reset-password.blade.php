@extends('partials.layouts.master')
@section('title','Restablecer contraseña')

@section('content')
<div class="card">
  <div class="card-body">
    <h5 class="mb-3">Restablecer contraseña</h5>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        {{-- 1) token oculto que llega por la URL --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- 2) email --}}
        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email"
                value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
            <label for="email">Correo electrónico</label>
        </div>

        {{-- 3) password y confirmación --}}
        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password"
                placeholder="Nueva contraseña" required>
            <label for="password">Nueva contraseña</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                placeholder="Confirma la contraseña" required>
            <label for="password_confirmation">Confirmar contraseña</label>
        </div>

        <button class="btn btn-primary w-100">Guardar contraseña</button>
    </form>

  </div>
</div>
@endsection
