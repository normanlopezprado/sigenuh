@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Plantas')
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
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($niveles as $n)
                        <tr>
                            <td>
                                {{ $n->name }}
                            </td>
                            <td>
                                {{ $n->status ? 'Activo' : 'Inactivo' }}
                            </td>
                            <td class="d-flex gap-2">
                                @can('niveles.edit')
                                <a class="btn btn-sm btn-warning" href="{{ route('niveles.edit', $n) }}">Editar</a>
                                @endcan

                                @can('niveles.delete')
                                <form method="POST" action="{{ route('niveles.destroy', $n->id) }}" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                Sin registros,
                                @can('niveles.create')
                                <a href="{{ route('niveles.create') }}" class="btn btn-primary">Nuevo</a>
                                @endcan
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

    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>


    <!-- Datatable init -->
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

@endsection





