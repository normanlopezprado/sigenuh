@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Ingredientes')
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
                        <th>Categoría</th>
                        <th>Unidad</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ingredients as $ing)
                        <tr>
                            <td>{{ $ing->name }}</td>
                            <td>{{ $ing->category }}</td>
                            <td>{{ $ing->unit }}</td>
                            <td>
                                @can('ingredients.edit')
                                <a href="{{ route('ingredients.edit', $ing) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('ingredients.destroy', $ing->id) }}" method="POST" style="display:inline-block">
                                @endcan
                                @can('ingredients.delete')
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar ingrediente?')">Desactivar</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                Sin registros
                                @can('ingredients.create')
                                <a href="{{ route('ingredients.create') }}" class="btn btn-primary">Nuevo</a>
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <!-- Datatable init -->
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

@endsection
