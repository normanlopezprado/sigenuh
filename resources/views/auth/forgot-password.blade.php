@extends('partials.layouts.master')
@section('title','Recuperar contraseña')

@section('content')
<div class="card">
  <div class="card-body">
    <h5 class="mb-3">¿Olvidaste tu contraseña?</h5>
    <p>Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="form-floating mb-3">
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               id="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
        <label for="email">Correo electrónico</label>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <button class="btn btn-primary w-100">Enviar enlace</button>
    </form>
  </div>
</div>
@endsection
