
@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Servicios -> Camas -> Crear cama')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hospitales -> Creación de camas</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success mt-2">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="col-md-12 form-floating form-label">
                        <form action="{{ route('beds.store') }}" method="POST">
                            @csrf
                            <div class="col-md-12 form-floating form-label">
                                <div class="col-md-6 form-label">
                                    <label for="code" class="form-label">Número</label>
                                    <input type="text" name="code" id="code" class="form-control"
                                           value="{{ old('code') }}" required>
                                    @error('code')
                                    <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 form-label">
                                    <label for="status" class="form-label">Estado</label>
                                    <select name="status" id="status" class="form-select mb-4">
                                        <option value="Disponible" {{ old('status') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                                        <option value="Ocupada" {{ old('status') == 'Ocupada' ? 'selected' : '' }}>Ocupada</option>
                                    </select>
                                    @error('status')
                                    <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 form-label">
                                    <label for="notes" class="form-label">Notas</label>
                                    <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
                                </div>
                                <div class="col-md-6 form-label">
                                    <label class="form-label">Selección del Servicio</label>
                                    <select name="hospital_floor_service_id" class="form-select mb-4" required>
                                        <option value="">— Seleccione —</option>
                                        @foreach($hfs as $row)
                                            @php
                                                $nivel = $row->hospitalFloor?->nivel?->name ?? 'Nivel';
                                                $serv  = $row->service?->name . ' ' . $row->service?->category . ' (' . $row->service?->abbreviation . ')';
                                            @endphp
                                            <option value="{{ $row->id }}" @selected(old('hospital_floor_service_id')===$row->id)>
                                                {{ $nivel }} —> {{ $serv }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('hospital_floor_service_id')
                                    <div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="{{ route('beds.index') }}" class="btn btn-danger">Cancelar</a>
                            </div>
                        </form>

                        @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const all = document.getElementById('checkAll');
                                    const items = document.querySelectorAll('.service-item');
                                    all?.addEventListener('change', e => items.forEach(el => el.checked = e.target.checked));
                                });
                            </script>
                        @endpush
                    </div>

                </div>
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


