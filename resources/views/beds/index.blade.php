@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Servicios -> Camas')
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
                        <th>Número</th>
                        <th>Estado</th>
                        <th>Notas</th>
                        <th>Servicio</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($beds as $bed)
                        <tr>
                            <td>{{ $bed->code }}</td>
                            <td>{{ $bed->status }}</td>
                            <td>{{ $bed->notes }}</td>
                            <td>
                                {{ $bed->hospitalFloorService?->hospitalFloor->hospital->name }}
                                ->
                                {{ $bed->hospitalFloorService?->hospitalFloor->nivel->name }}
                                ->
                                {{ $bed->hospitalFloorService?->service->name  }}
                            </td>
                            <td>
                                @can('beds.edit')
                                <a href="{{ route('beds.edit', $bed) }}" class="btn btn-sm btn-warning">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                Sin registros,
                                @can('beds.create')
                                <a href="{{ route('beds.create') }}" class="btn btn-primary">Nuevo</a>
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
