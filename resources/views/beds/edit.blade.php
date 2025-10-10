    <form action="{{ route('beds.update', $bed) }}" method="POST">
        @csrf
        @method('PUT')


    </form>
    @extends('partials.layouts.master2')

    @section('title', 'sigenhuh')
    @section('sub-title', 'Hospitales -> Servicios -> Camas -> Editar cama' )
    @section('pagetitle', 'Inicio')
    @section('buttonTitle', 'Share')
    @section('modalTarget', 'shareModal')

    @section('css')
        <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/leaflet/leaflet.css') }}">
    @endsection

    @section('content')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('beds.update', $bed) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="col-md-12 form-floating form-label">
                <div class="col-md-6 form-label">
                    <label for="code" class="form-label">Número</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $bed->code) }}" required>
                    @error('code') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 form-label">
                    <label for="status" class="form-label">Estado</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Disponible" {{ $bed->status == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="Ocupada" {{ $bed->status == 'Ocupada' ? 'selected' : '' }}>Ocupada</option>
                    </select>
                    @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 form-label">
                    <label for="notes" class="form-label">Notas</label>
                    <textarea name="notes" id="notes" class="form-control">{{ old('notes', $bed->notes) }}</textarea>
                </div>
                <div class="col-md-6 form-label">
                    <label class="form-label">Selección del Servicio</label>
                    <select name="hospital_floor_service_id" class="form-control" required>
                        <option value="">— Seleccione —</option>
                        @foreach($hfs as $row)
                            @php
                                $nivel = $row->hospitalFloor?->nivel?->name ?? 'Nivel';
                                $serv  = $row->service?->name . ' ' . $row->service?->category . ' (' . $row->service?->abbreviation . ')';
                            @endphp
                            <option value="{{ $row->id }}" @selected(old('hospital_floor_service_id',$bed->hospital_floor_service_id)===$row->id)>
                                {{ $nivel }} —> {{ $serv }}
                            </option>
                        @endforeach
                    </select>
                    @error('hospital_floor_service_id')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('beds.index') }}" class="btn btn-danger">Cancelar</a>
            </div>
        </form>
    @endsection

    @section('js')
        <!-- Air Datepicker js -->
        <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
        <!-- Form-layout init -->
        <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>
        <!-- App js -->
        <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
    @endsection
