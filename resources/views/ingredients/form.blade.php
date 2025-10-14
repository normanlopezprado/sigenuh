{{-- resources/views/ingredients/form.blade.php --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
    @csrf
    @isset($method)
        @method($method)
    @endisset

    {{-- Errores globales --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-12 col-lg-8 mx-auto">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    {{ isset($ingredient) ? 'Editar Ingrediente' : 'Nuevo Ingrediente' }}
                </h5>
            </div>
            <div class="card-body">

                {{-- Nombre --}}
                <div class="form-floating mb-3">
                    <input type="text"
                        name="name"
                        id="name"
                        placeholder="Ingrediente"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $ingredient->name ?? '') }}"
                        required>
                    <label for="name">Nombre</label>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                {{-- Categoría --}}
                <div class="form-floating mb-3">
                    <select name="category" id="category"
                            class="form-select @error('category') is-invalid @enderror" required>
                        <option value="" disabled {{ old('category', $ingredient->category ?? '') === '' ? 'selected' : '' }}>
                            Selecciona una categoría
                        </option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(old('category', $ingredient->category ?? '') === $cat)>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                    <label for="category">Categoría</label>
                    @error('category')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>



                {{-- Unidad --}}
                <div class="form-floating mb-3">
                    <select name="unit" id="unit"
                            class="form-select @error('unit') is-invalid @enderror" required>
                        <option value="" disabled {{ old('unit', $ingredient->unit ?? '') === '' ? 'selected' : '' }}>
                            Selecciona una unidad
                        </option>
                        @foreach($unitsMap as $abbr => $label)
                            <option value="{{ $abbr }}" @selected(old('unit', $ingredient->unit ?? '') === $abbr)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <label for="unit">Unidad</label>
                    @error('unit')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>




                {{-- Notas --}}
                <div class="form-floating mb-3">
                    <textarea name="notes"
                        id="notes"
                        class="form-control @error('notes') is-invalid @enderror"
                        style="height: 120px">{{ old('notes', $ingredient->notes ?? '') }}</textarea>
                    <label for="notes">Nota</label>
                    @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    {{ $btnText ?? (isset($ingredient) ? 'Actualizar' : 'Guardar') }}
                </button>
                @isset($cancelUrl)
                    <a href="{{ $cancelUrl }}" class="btn btn-danger">Volver</a>
                @endisset
            </div>
        </div>
    </div>
</form>
