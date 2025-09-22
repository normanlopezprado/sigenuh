@csrf

<div class="mb-3">
</div>
<div class="mb-3">
</div>
<div class="mb-3">
</div>
<div class="mb-3">
</div>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Ingrediente</h5>
            </div>
            <div class="card-body">
                <div class="col-md-12 form-floating form-label">
                    <div class="col-md-6 form-floating form-label">
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $ingredient->name ?? '') }}" required>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                        <label class="form-label">Nombre</label>
                    </div>
                    <div class="col-md-6 form-floating form-label">
                        <input type="text" name="category" class="form-select"
                               value="{{ old('category', $ingredient->category ?? '') }}" required>
                        <label class="form-label">Categoría</label>
                        @error('category')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-floating form-label">
                        <select name="unit" class="form-select" required>
                            @foreach($units as $u)
                                <option value="{{ $u }}" @selected(old('unit', $ingredient->unit ?? '')===$u)>{{ $u }}</option>
                            @endforeach
                        </select>
                        <label class="form-label">Unidad</label>
                        @error('unit')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-floating form-label">
                        <textarea name="notes" class="form-control">{{ old('notes', $ingredient->notes ?? '') }}</textarea>
                        <label class="form-label">Notas</label>
                        @error('notes')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
