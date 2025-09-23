<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Hospital</h5>
            </div>
            <div class="card-body">
                <div class="col g-4">
                    <div class="col-md-12 form-floating form-label">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nombre del hospital" value="{{ old('name', $hospital->name) }}" required>
                        <label for="name" class="form-label">Nombre del hospital</label>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12 form-floating form-label">
                        <textarea name="description" id="description" class="form-control" placeholder="Descripción" rows="3">{{ old('description', $hospital->description) }}</textarea>
                        <label for="description" class="form-label">Descripción</label>

                    </div>
                    <div class="col-md-12 form-floating form-label">
                        <input type="text" class="form-control " id="address" name="address" placeholder="Dirección" value="{{ old('address', $hospital->address) }}">
                        <label for="address" class="form-label">Dirección</label>

                    </div>
                    <div class="col-md-12 form-floating form-label">
                        <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@ejemplo.com" value="{{ old('category', $hospital->email) }}" >
                        <label for="email" class="form-label">ejemplo@ejemplo.com</label>


                    </div>
                    <div class="col-md-12 form-floating form-label">
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Teléfono" value="{{ old('category', $hospital->phone) }}" >
                        <label for="email" class="form-label">Teléfono</label>

                    </div>
                    <div class="col-md-12 form-label">
                        <label class="form-label">Logo (jpg, png, webp | máx 2MB)</label>
                        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        @if(!empty($hospital?->logo_url))
                            <div class="mt-2">
                                <img src="{{ $hospital->logo_url }}" alt="logo" style="height:60px">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-12 form-label">
                        <label class="form-label">Icon (ico, png, webp | máx 2MB)</label>
                        <input type="file" name="icon" class="form-control" accept=".ico,.png,.webp">
                        @if(!empty($hospital?->icon_url))
                            <div class="mt-2">
                                <img src="{{ $hospital->icon_url }}" alt="icon" style="height:32px">
                            </div>
                        @endif
                    </div>

                    <div class="row g-3 form-label">
                        <div class="col-md-6">
                            <label class="form-label">Latitud</label>
                            <input type="number" step="any" name="latitude" id="latitude" class="form-control"
                                   value="{{ old('latitude', $hospital->latitude ?? '') }}">
                        </div>
                        <div class="col-md-6 form-label">
                            <label class="form-label">Longitud</label>
                            <input type="number" step="any" name="longitude" id="longitude" class="form-control"
                                   value="{{ old('longitude', $hospital->longitude ?? '') }}">
                        </div>
                    </div>

                    {{-- Mapa Leaflet --}}
                    <div class="col-md-12 form-floating form-label">
                        <div class="col-12 col-lg-12">
                            <div class="card mb-0 h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Mapa</h5>
                                </div>
                                <div class="card-body">
                                    <div class="min-h-320px" id="leaflet_map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3 form-label mt-3">
                        <h5>Horarios de comida</h5>

                        <!-- Breakfast -->
                        <div class="col-md-4">
                            <label class="form-label">Inicio de recolección de desayuno</label>
                            <input type="time" name="breakfast_collection_start" class="form-control"
                                   value="{{ old('breakfast_collection_start', $hospital->breakfast_collection_start?->format('H:i')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fin de recolección de desayuno</label>
                            <input type="time" name="breakfast_collection_end" class="form-control"
                                   value="{{ old('breakfast_collection_end', $hospital->breakfast_collection_end?->format('H:i')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hora de desayuno</label>
                            <input type="time" name="breakfast_time" class="form-control"
                                   value="{{ old('breakfast_time', $hospital->breakfast_time?->format('H:i')) }}">
                        </div>

                        <!-- Lunch -->
                        <div class="col-md-4">
                            <label class="form-label">Inicio de recolección de almuerzo</label>
                            <input type="time" name="lunch_collection_start" class="form-control"
                                   value="{{ old('lunch_collection_start', $hospital->lunch_collection_start?->format('H:i')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fin de recolección de almuerzo</label>
                            <input type="time" name="lunch_collection_end" class="form-control"
                                   value="{{ old('lunch_collection_end', $hospital->lunch_collection_end?->format('H:i')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hora de almuerzo</label>
                            <input type="time" name="lunch_time" class="form-control"
                                   value="{{ old('lunch_time', $hospital->lunch_time?->format('H:i')) }}">
                        </div>

                        <!-- Dinner -->
                        <div class="col-md-4">
                            <label class="form-label">Inicio de recolección de cena</label>
                            <input type="time" name="dinner_collection_start" class="form-control"
                                   value="{{ old('dinner_collection_start', $hospital->dinner_collection_start?->format('H:i')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fin de recolección de cena</label>
                            <input type="time" name="dinner_collection_end" class="form-control"
                                   value="{{ old('dinner_collection_end', $hospital->dinner_collection_end?->format('H:i')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hora de cena</label>
                            <input type="time" name="dinner_time" class="form-control"
                                   value="{{ old('dinner_time', $hospital->dinner_time?->format('H:i')) }}">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('hospitales.index') }}" class="btn btn-danger">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</div>




@section('js')

    <!-- leaflet-map js -->
    <script src="{{ asset('assets/libs/leaflet/leaflet.js') }}"></script>

    <!-- leaflet-map init -->
    <script src="{{ asset('assets/js/maps/leaflet-map.init.js') }}"></script>

@endsection
