    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Planta</h5>
                </div>
                <div class="card-body">
                        <div class="col-md-12 form-floating form-label">
                            <div class="col-md-6 form-floating form-label">
                                <input type="text" class="form-control" id="name" name="name" placeholder="1er Piso - 2do Piso - 3er Piso - 4to Piso" value="{{ old('name', $nivel->name) }}" required>
                                <label for="name" class="form-label">1er Piso - 2do Piso - 3er Piso - 4to Piso</label>
                                @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a href="{{ route('niveles.index') }}" class="btn btn-danger">Cancelar</a>
                        </div>
                </div>
            </div>
        </div>
    </div>
