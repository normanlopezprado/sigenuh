
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Menús Editar')
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
                    <h5 class="card-title mb-0">Editar menú</h5>
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
                        <form method="POST" action="{{ route('menus.update', $menu) }}">
                            @csrf
                            @method('PUT')
                            <div class="col-md-12 form-floating form-label">
                                <input type="text" name="name" class="form-control" value="{{ old('name',$menu->name) }}" required>
                                <label class="form-label">Nombre</label>
                            </div>
                            <div class="col-md-12 form-floating form-label">
                                <select name="category" class="form-select" required>
                                    @foreach($categories as $c)
                                        <option value="{{ $c }}" @selected(old('category', $menu->category ?? '')===$c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label">Categoría</label>
                            </div>
                            <div class="col-md-12 form-floating form-label">
                                <select name="diet_type" class="form-select">
                                    @foreach($dietOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('diet_type', $menu->diet_type)===$value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label">Tipo de dieta</label>
                            </div>
                            <div class="col-md-12 form-floating form-label">
                                <textarea name="description" class="form-control">{{ old('description',$menu->description) }}</textarea>
                                <label class="form-label">Descripción</label>
                            </div>
                            <div class="col-md-12 form-floating form-label">
                                <textarea name="notes" class="form-control">{{ old('notes',$menu->notes) }}</textarea>
                                <label class="form-label">Notas</label>
                            </div>
                            <hr class="my-4">

                            <h4>Ingredientes del menú</h4>
                            <p class="text-muted">Agrega ingredientes, cantidades y marca si son opcionales. Al guardar se sincroniza todo.</p>

                            <table class="table" id="ingredients-table">
                                <thead>
                                <tr>
                                    <th style="width:40%">Ingrediente</th>
                                    <th style="width:15%">Cantidad</th>
                                    <th style="width:15%">Opcional</th>
                                    <th style="width:25%">Notas</th>
                                    <th style="width:5%"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $oldIngs = old('ingredient_id', $current->pluck('id')->toArray());
                                    $oldQty  = old('qty', $current->pluck('pivot.qty')->toArray());
                                    $oldOpt  = old('is_optional', $current->pluck('pivot.is_optional')->toArray());
                                    $oldNot  = old('pivot_notes', $current->pluck('pivot.notes')->toArray());
                                @endphp

                                @if(!empty($oldIngs))
                                    @foreach($oldIngs as $i => $ingId)
                                        <tr>
                                            <td>
                                                <select name="ingredient_id[]" class="form-select" required>
                                                    <option value="">— Seleccione —</option>
                                                    @foreach($ingredients as $ing)
                                                        <option value="{{ $ing->id }}" @selected($ingId===$ing->id)>{{ $ing->name }} ({{ $ing->unit }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.001" min="0" name="qty[]" class="form-control"
                                                    value="{{ $oldQty[$i] ?? 0 }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="is_optional[{{ $i }}]" 
                                                value="1" {{ !empty($oldOpt[$i]) ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <input type="text" name="pivot_notes[]" class="form-control" 
                                                value="{{ $oldNot[$i] ?? '' }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">✖</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>

                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">➕ Agregar ingrediente</button>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary">Guardar</button>
                                <a href="{{ route('menus.index') }}" class="btn btn-sm btn-danger">Cancelar</a>
                            </div>
                        </form>

                        @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const addBtn = document.getElementById('add-row');
                                    const tbody  = document.querySelector('#ingredients-table tbody');

                                    addBtn.addEventListener('click', () => {
                                        const index = tbody.querySelectorAll('tr').length;
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                        <td>
                                            <select name="ingredient_id[]" class="form-control" required>
                                            <option value="">— Seleccione —</option>
                                            @foreach($ingredients as $ing)
                                                                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                                            @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" step="0.001" min="0" name="qty[]" class="form-control" value="0">
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <input type="checkbox" name="is_optional[${index}]" value="1">
                                        </td>
                                        <td>
                                            <input type="text" name="pivot_notes[]" class="form-control" value="">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">✖</button>
                                        </td>
                                        `;
                                        tbody.appendChild(row);
                                    });
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


