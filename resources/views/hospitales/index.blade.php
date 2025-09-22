@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')

    <div class="row g-4">
        <div class="col-12">
            <div class="card mb-0 h-100">
                <table class="data-table-basic table-hover align-middle table table-nowrap w-100">
                    <thead class="bg-light bg-opacity-30">
                    <tr>
                        <th>Logo</th>
                        <th>Icono</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th></th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($hospitals as $h)
                        <tr>
                            <td>
                                @if($h->logo_url)
                                    <img src="{{ $h->logo_url }}" alt="logo" style="height:40px">
                                @endif
                            </td>
                            <td>
                                @if($h->icon_url)
                                    <img src="{{ $h->icon_url }}" alt="icono" style="height:40px">
                                @endif
                            </td>
                            <td>{{ $h->name }}</td>
                            <td>{{ $h->address }}</td>
                            <td>{{ $h->email }}</td>
                            <td>{{ $h->phone }}</td>
                            <td>
                                @if(!is_null($h->latitude) && !is_null($h->longitude))
                                    {{ $h->latitude }}, {{ $h->longitude }}
                                @endif
                            </td>
                            <td class="d-flex gap-2">
                                @can('hospitales.edit')
                                <a class="btn btn-sm btn-warning" href="{{ route('hospitales.edit', $h) }}">Editar</a>
                                @endcan
                                @can('hospitales.delete')
                                <form method="POST" action="{{ route('hospitales.destroy', $h) }}" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                Sin registros
                                @can(hospitales.create)
                                <a href="{{ route('hospitales.create') }}" class="btn btn-primary">Nuevo</a>
                                @can(hospitales.create)
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

    <!-- Datatable js -->
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <!-- Datatable init -->
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

@endsection
{{ $hospitals->links() }}
