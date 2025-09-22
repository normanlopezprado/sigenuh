@extends('partials.layouts.master')

@section('title','SIGENUH')
@section('sub-title','Accesos -> Usuarios')
@section('pagetitle','Inicio')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Usuarios</h5>
    @can('users.create')
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Añadir</a>
    @endcan
  </div>

  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th>Avatar</th>
          <th>Nombre</th>
          <th>Usuario</th>
          <th>Email</th>
          <th>Hospital</th>
          <th>Rol</th>
          <th>Verificación de email</th>
          <th>Creado</th>
          <th>Actualizado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      @forelse($users as $u)
        <tr>
          <td><img src="{{ $u->avatar_url }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;"></td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->user }}</td>
          <td>{{ $u->email }}</td>
          <td>{{ $u->hospital?->name ?? '—' }}</td>
          <td>
            @php($r = $u->roles->pluck('name')->first())
            @if($r)
                <span class="badge bg-primary-subtle text-primary">{{ $r }}</span>
            @else
                <span class="text-muted">—</span>
            @endif
          </td>
          <td>{{ $u->email_verified_at ? $u->email_verified_at->format('Y-m-d H:i') : 'No verificado' }}</td>
          <td>{{ $u->created_at?->format('Y-m-d') }}</td>
          <td>{{ $u->updated_at?->format('Y-m-d') }}</td>
          <td class="d-flex gap-2">
            @can('users.edit')
            <a class="btn btn-sm btn-warning" href="{{ route('usuarios.edit',$u) }}">Editar</a>
            @endcan
            
            @can('users.delete')
            <form method="POST" action="{{ route('usuarios.destroy',$u) }}">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">
                Eliminar
            </button>
            </form>
            @endcan
          </td>
        </tr>
      @empty
        <tr>
            <td colspan="8">
                Sin registros.
                @can('users.create')
                <a href="{{ route('usuarios.create') }}">
                    Crear
                </a>
                @endcan
            </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="card-footer">
    {{ $users->links() }}
  </div>
</div>
@endsection
