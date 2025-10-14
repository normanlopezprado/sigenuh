@extends('partials.layouts.master')

@section('title','SIGENUH')
@section('sub-title','Detalle de Usuario')
@section('pagetitle','Inicio')

@section('content')
<div class="card">
  <div class="card-header d-flex align-items-center gap-3">
    <img src="{{ $usuario->avatar_url }}"
      alt="Avatar de {{ $usuario->name }}"
      class="rounded-circle"
      style="width:64px;height:64px;object-fit:cover;">
    <h5 class="card-title mb-0">Información del Usuario</h5>
  </div>

  <div class="card-body">
    <p><strong>Nombre:</strong> {{ $usuario->name }}</p>
    <p><strong>Usuario:</strong> {{ $usuario->user }}</p>
    <p><strong>Email:</strong> {{ $usuario->email }}</p>
    <p><strong>Hospital:</strong> {{ $usuario->hospital?->name ?? '—' }}</p>
    <p><strong>Email verificado:</strong>
      {{ $usuario->email_verified_at ? $usuario->email_verified_at->format('Y-m-d H:i') : 'No verificado' }}
    </p>
    <p><strong>Creado:</strong> {{ $usuario->created_at?->format('Y-m-d H:i') }}</p>
    <p><strong>Actualizado:</strong> {{ $usuario->updated_at?->format('Y-m-d H:i') }}</p>
  </div>
</div>
@endsection
