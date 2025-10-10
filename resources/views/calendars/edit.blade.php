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
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Evento</h5>
                </div>
                <div class="card-body">
                    <div class="col g-4">
                        <form method="POST" action="{{ route('calendars.update', $calendar) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Fecha</label>
                                    <input type="date"
                                           name="date"
                                           class="form-control @error('date') is-invalid @enderror"
                                           value="{{ old('date', $calendar->date->format('Y-m-d')) }}"
                                           required disabled>
                                    @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">* La fecha una vez asignada no se puede cambiar.</small>
                                </div>

                                <div class="col-md-9">
                                    <label class="form-label d-block">Menú asignado</label>
                                    @if($calendar->menu)
                                        <div class="p-2 border rounded bg-light">
                                            <strong>{{ $calendar->menu->name }}</strong>
                                            <span class="text-muted"> · {{ ucfirst($calendar->menu->category) }}</span>
                                            @if($calendar->menu->diet_type)
                                                <span class="text-muted"> · {{ $calendar->menu->diet_type }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">Este calendario no tiene menú asignado.
                                        </div>
                                    @endif
                                    <small class="text-muted">* El menú una vez asignado no se puede cambiar.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notas (opcional)</label>
                                    <textarea name="notes" rows="3"
                                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $calendar->notes) }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- OPCIONALES DEL MENÚ --}}
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">Ingredientes opcionales</h5>
                                <div>
                                    <label class="form-check-label me-2">
                                        <input type="checkbox" id="checkAll" class="form-check-input">
                                        Seleccionar todos
                                    </label>
                                </div>
                            </div>

                            @if($optionalItems->isEmpty())
                                <div class="alert alert-info">
                                    El menú seleccionado no tiene ingredientes marcados como <em>opcionales</em>.
                                </div>
                            @else
                                <div class="row g-2">
                                    @foreach($optionalItems as $opt)
                                        <div class="col-md-6">
                                            <label class="form-check-label me-2">
                                                <input
                                                    type="checkbox"
                                                    name="selected_menu_ingredient_id[]"
                                                    class="form-check-input optional-item"
                                                    value="{{ $opt->id }}"
                                                    @checked(in_array($opt->id, old('selected_menu_ingredient_id', $selectedOptionalIds)))
                                                >
                                                <span>
                                                    <strong>{{ $opt->ingredient?->name ?? 'Ingrediente' }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                      Menú: {{ $opt->menu?->name ?? '-' }}
                                                        @if(!empty($opt->qty))
                                                            · Cant: {{ rtrim(rtrim(number_format($opt->qty, 3, '.', ''), '0'), '.') }}
                                                        @endif
                                                        @if(!empty($opt->unit))
                                                            · {{ $opt->unit }}
                                                        @endif
                                                    </small>
                                                  </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-3 d-flex gap-2">
                                <button class="btn btn-primary">Guardar cambios</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Volver</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const all = document.getElementById('checkAll');
            const items = document.querySelectorAll('.optional-item');
            if (all) {
                all.addEventListener('change', e => {
                    items.forEach(chk => chk.checked = e.target.checked);
                });
            }
        });
    </script>
@endsection

@section('js')

    <!-- Air Datepicker js -->
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>

    <!-- Form-layout init -->
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>

    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection

