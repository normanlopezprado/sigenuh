@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Hospitales -> Carritos')
@section('pagetitle', 'Carritos')
@section('buttonTitle', 'Share')

@section('css')
@endsection

@section('content')

<div class="row g-4">
    <div class="col-12">

        {{-- Botón/checkbox para mostrar/ocultar inactivos --}}
        <form method="get" class="mb-2 d-flex justify-content-end">
            <label class="form-check">
                <input type="checkbox" name="show_inactive" class="form-check-input" value="1"
                       {{ $showInactive ? 'checked' : '' }} onchange="this.form.submit()">
                <span class="form-check-label">Mostrar inactivos</span>
            </label>
        </form>

        <div class="card mb-0 h-100">
            <table class="data-table-basic table-hover align-middle table table-nowrap w-100">
                <thead class="bg-light bg-opacity-30">
                <tr>
                    <th>Nombre</th>
                    <th>Apodo</th>
                    <th>Creado</th>
                    <th>Actualizado</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody>
                @forelse($carts as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->code_name }}</td>
                        <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $c->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="d-flex gap-2">
                            <a class="btn btn-sm btn-warning" href="{{ route('carts.edit', $c) }}">Editar</a>
                            <form method="POST" action="{{ route('carts.destroy', $c) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('¿Deshabilitar?')">Deshabilitar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Sin registros,
                            <a href="{{ route('carts.create') }}" class="btn btn-primary">Nuevo</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('partials.social-share-modal')

@endsection

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
