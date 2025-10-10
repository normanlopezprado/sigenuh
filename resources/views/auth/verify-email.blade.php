@extends('partials.layouts.master')
@section('title','Verifica tu correo')

@section('content')
<div class="card">
  <div class="card-body">
    <h5 class="mb-2">Verifica tu correo electrónico</h5>
    <p>Te enviamos un enlace de verificación a tu correo. Si no lo recibiste, puedes solicitar otro.</p>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button class="btn btn-primary">Reenviar enlace</button>
    </form>
  </div>
</div>
@endsection
