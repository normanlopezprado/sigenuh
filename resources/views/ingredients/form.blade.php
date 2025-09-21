@csrf

<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="name" class="form-control"
           value="{{ old('name', $ingredient->name ?? '') }}" required>
    @error('name')<div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Categoría</label>
    <input type="text" name="category" class="form-control"
           value="{{ old('category', $ingredient->category ?? '') }}" required>
    @error('category')<div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Unidad</label>
    <select name="unit" class="form-control" required>
        @foreach($units as $u)
            <option value="{{ $u }}" @selected(old('unit', $ingredient->unit ?? '')===$u)>{{ $u }}</option>
        @endforeach
    </select>
    @error('unit')<div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Estado</label>
    <select name="status" class="form-control" required>
        <option value="1" @selected(old('status', $ingredient->status ?? 1)==1)>Activo</option>
        <option value="0" @selected(old('status', $ingredient->status ?? 1)==0)>Inactivo</option>
    </select>
    @error('status')<div class="text-danger">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Notas</label>
    <textarea name="notes" class="form-control">{{ old('notes', $ingredient->notes ?? '') }}</textarea>
    @error('notes')<div class="text-danger">{{ $message }}</div>@enderror
</div>
