@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Hospitales -> Beneficiarios')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')

    <div class="row g-4">
        <div class="col-12">
            {{-- Filtro para mostrar u ocultar inactivos --}}
                <form method="get">
                    <label class="">
                        <input type="checkbox" name="show_inactive" class="form-check-input"
                            value="1" {{ request('show_inactive') ? 'checked' : '' }}
                            onchange="this.form.submit()">
                        <span class="form-check-label">Mostrar inactivos</span>
                    </label>
                </form>
            <div class="card mb-0 h-100">
                
                <table class="data-table-basic table-hover align-middle table table-nowrap w-100">
                    <thead class="bg-light bg-opacity-30">
                    <tr>
                        <th>Beneficiario</th>
                        <th>Puesto</th>
                        <th>Estado</th>
                        <th>Hospital</th>
                        <th>Creado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($beneficiaries as $b)
                        <tr>
                            <td>
                                <a href="{{ route('staff-beneficiaries.show', $b) }}">{{ $b->full_name }}</a>
                            </td>
                            <td>{{ $b->job_title }}</td>
                            <td>
                                <span class="badge {{ $b->status ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $b->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>{{ optional($b->hospital)->name ?? '—' }}</td>
                            <td class="text-muted">{{ optional($b->created_at)->format('Y-m-d H:i') }}</td>
                            {{-- Acciones en la tabla --}}
                            <td class="d-flex gap-2 justify-content-end">
                                <a class="btn btn-sm btn-warning" href="{{ route('staff-beneficiaries.edit', $b) }}">Editar</a>
                                @if($b->status)
                                    <form method="POST" action="{{ route('staff-beneficiaries.toggle-status', $b) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-secondary" onclick="return confirm('¿Desactivar este beneficiario?')">
                                            Desactivar
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('staff-beneficiaries.toggle-status', $b) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-success" onclick="return confirm('¿Activar este beneficiario?')">
                                            Activar
                                        </button>
                                    </form>
                                @endif
                                
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                Sin registros,
                                
                                <a href="{{ route('staff-beneficiaries.create') }}" class="btn btn-primary">Nuevo</a>
                                
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
