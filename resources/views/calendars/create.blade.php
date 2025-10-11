@extends('partials.layouts.master2')

@section('title', 'sigenhuh')
@section('sub-title', 'Crear evento de calendario' )
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')
@section('modalTarget', 'shareModal')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
@endsection

@section('content')
    @if ($errors->any())

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Evento</h5>
                </div>
                <div class="card-body">
                    <div class="col g-4">
                        <p class="text-muted mb-4">Selecciona la fecha y el menú. Los opcionales se agregan en la edición.</p>
                        <form method="GET" action="{{ route('calendars.create') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Categoría</label>
                                <select name="category" class="form-select">
                                    <option value="">-- Todas --</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c }}" @selected($cat===$c)>{{ ucfirst($c) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de dieta</label>
                                <select name="diet_type" class="form-select">
                                    <option value="">-- Todas --</option>
                                    @foreach($dietTypes as $d)
                                        <option value="{{ $d }}" @selected($diet===$d)>{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-outline-primary w-100">Filtrar</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('calendars.store') }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                           value="{{ old('date', $date ?? '') }}" required>
                                    @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Menú</label>
                                    <select name="menu_id" class="form-select @error('menu_id') is-invalid @enderror" required>
                                        <option value="">-- Selecciona un menú --</option>
                                        @forelse($menus as $m)
                                            <option value="{{ $m->id }}" @selected(old('menu_id')===$m->id)>
                                                {{ $m->name }}
                                                — {{ ucfirst($m->category) }}{{ $m->diet_type ? ' · '.$m->diet_type : '' }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No hay menús con los filtros actuales</option>
                                        @endforelse
                                    </select>
                                    @error('menu_id')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notas (opcional)</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button class="btn btn-success">Guardar</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@section('js')

    <!-- Air Datepicker js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- Form-layout init -->
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection

