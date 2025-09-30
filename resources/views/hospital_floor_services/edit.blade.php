@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Servicios -> Asignar servicios a plantas')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-8"><!-- un poco más ancho para la lista doble -->
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Asignar servicios a plantas</h5>
                </div>

                @can('hospital-floor-services.edit')
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

                    <div class="form-label">
                        {{-- Selector de piso AUTOMÁTICO --}}
                        <form method="GET" action="{{ route('hospital-floor-services.edit') }}" class="row g-2 mb-3">
                            <div class="col-12">
                                <select name="floor" class="form-select" onchange="this.form.submit()">
                                    @foreach($floors as $f)
                                        <option value="{{ $f->id }}" @selected(optional($selectedFloor)->id === $f->id)>
                                            {{ $f->nivel?->name ?? 'Piso' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        <hr>

                        @if($selectedFloor)
                            <form method="POST" action="{{ route('hospital-floor-services.update') }}" id="svcForm">
                                @csrf
                                <input type="hidden" name="floor" value="{{ $selectedFloor->id }}">

                                {{-- Dual List --}}
                                <div class="row g-3">
                                    {{-- Columna disponibles --}}
                                    <div class="col-12 col-md-5">
                                        <div class="mb-2">
                                            <input type="text" id="filterAvailable" class="form-control" placeholder="Filtrar disponibles...">
                                        </div>
                                        <div class="card">
                                            <div class="card-header py-2">
                                                <strong>Disponibles</strong>
                                            </div>
                                            <div class="card-body p-0">
                                                <ul id="listAvailable" class="list-group list-group-flush" style="height: 320px; overflow:auto;">
                                                    @foreach($services as $s)
                                                        @php
                                                            $isSelected = in_array($s->id, old('services', $selectedServiceIds));
                                                            $text = trim(($s->name ?? '').' '.(!empty($s->abbreviation) ? '(' . $s->abbreviation . ')' : ''));
                                                        @endphp
                                                        @if(!$isSelected)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                                                data-id="{{ $s->id }}"
                                                                data-text="{{ strtolower($text) }}"
                                                                role="button">
                                                                <span class="me-2">{{ $s->name }}
                                                                    @if(!empty($s->abbreviation))
                                                                        <small class="text-muted">({{ $s->abbreviation }})</small>
                                                                    @endif
                                                                </span>
                                                                <i class="ri-arrow-right-line"></i>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Botonera --}}
                                    <div class="col-12 col-md-2 d-flex flex-column align-items-stretch justify-content-center gap-2">
                                        <button type="button" id="btnAddAll" class="btn btn-success">Añadir todos</button>
                                        <button type="button" id="btnAdd" class="btn btn-info">Añadir</button>
                                        <button type="button" id="btnRemove" class="btn btn-warning">Quitar</button>
                                        <button type="button" id="btnRemoveAll" class="btn btn-danger">Quitar todos</button>
                                    </div>

                                    {{-- Columna seleccionados --}}
                                    <div class="col-12 col-md-5">
                                        <div class="mb-2">
                                            <input type="text" id="filterSelected" class="form-control" placeholder="Filtrar seleccionados...">
                                        </div>
                                        <div class="card">
                                            <div class="card-header py-2">
                                                <strong>Seleccionados</strong>
                                            </div>
                                            <div class="card-body p-0">
                                                <ul id="listSelected" class="list-group list-group-flush" style="height: 320px; overflow:auto;">
                                                    @foreach($services as $s)
                                                        @php
                                                            $isSelected = in_array($s->id, old('services', $selectedServiceIds));
                                                            $text = trim(($s->name ?? '').' '.(!empty($s->abbreviation) ? '(' . $s->abbreviation . ')' : ''));
                                                        @endphp
                                                        @if($isSelected)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                                                data-id="{{ $s->id }}"
                                                                data-text="{{ strtolower($text) }}"
                                                                role="button">
                                                                <span class="me-2">{{ $s->name }}
                                                                    @if(!empty($s->abbreviation))
                                                                        <small class="text-muted">({{ $s->abbreviation }})</small>
                                                                    @endif
                                                                </span>
                                                                <i class="ri-arrow-left-line"></i>
                                                                {{-- input que se enviará --}}
                                                                <input type="hidden" name="services[]" value="{{ $s->id }}">
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @can('hospital-floor-services.update')
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <button class="btn btn-primary">Guardar</button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-danger">Cancelar</a>
                                </div>
                                @endcan
                            </form>
                        @endif

                        @push('scripts')
                            <script>
                                // Utilidad: alternar selección visual (clase active)
                                function toggleActive(li) {
                                    li.classList.toggle('active');
                                }

                                function clearActive(listEl) {
                                    listEl.querySelectorAll('.list-group-item.active').forEach(el => el.classList.remove('active'));
                                }

                                // Mover items de A -> B
                                function moveSelected(fromEl, toEl, toLeft) {
                                    const selected = fromEl.querySelectorAll('.list-group-item.active');
                                    selected.forEach(li => moveOne(li, toEl, toLeft));
                                }

                                function moveAll(fromEl, toEl, toLeft) {
                                    const all = fromEl.querySelectorAll('.list-group-item:not(.d-none)');
                                    all.forEach(li => moveOne(li, toEl, toLeft));
                                }

                                function moveOne(li, toEl, toLeft) {
                                    li.classList.remove('active');
                                    // Si va a "Seleccionados", agregamos input hidden; si vuelve a "Disponibles", lo removemos
                                    if (toEl.id === 'listSelected') {
                                        // agregar icono hacia la izquierda
                                        li.querySelector('i')?.remove();
                                        const icon = document.createElement('i');
                                        icon.className = 'ri-arrow-left-line';
                                        li.appendChild(icon);

                                        // crear hidden si no existe
                                        if (!li.querySelector('input[type="hidden"]')) {
                                            const hid = document.createElement('input');
                                            hid.type = 'hidden';
                                            hid.name = 'services[]';
                                            hid.value = li.getAttribute('data-id');
                                            li.appendChild(hid);
                                        }
                                    } else {
                                        // cambiar icono hacia la derecha
                                        li.querySelector('i')?.remove();
                                        const icon = document.createElement('i');
                                        icon.className = 'ri-arrow-right-line';
                                        li.appendChild(icon);
                                        // quitar hidden si existiera
                                        li.querySelector('input[type="hidden"]')?.remove();
                                    }
                                    toEl.appendChild(li);
                                }

                                function applyFilter(inputEl, listEl) {
                                    const q = (inputEl.value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                    listEl.querySelectorAll('.list-group-item').forEach(li => {
                                        const txt = (li.getAttribute('data-text') || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                        const match = !q || txt.includes(q);
                                        li.classList.toggle('d-none', !match);
                                    });
                                }

                                document.addEventListener('DOMContentLoaded', () => {
                                    const listAvailable = document.getElementById('listAvailable');
                                    const listSelected  = document.getElementById('listSelected');

                                    const filterAvailable = document.getElementById('filterAvailable');
                                    const filterSelected  = document.getElementById('filterSelected');

                                    const btnAddAll    = document.getElementById('btnAddAll');
                                    const btnAdd       = document.getElementById('btnAdd');
                                    const btnRemove    = document.getElementById('btnRemove');
                                    const btnRemoveAll = document.getElementById('btnRemoveAll');

                                    // Click para seleccionar item (toggle active)
                                    [listAvailable, listSelected].forEach(list => {
                                        list?.addEventListener('click', (e) => {
                                            const li = e.target.closest('.list-group-item');
                                            if (!li) return;
                                            toggleActive(li);
                                        });
                                    });

                                    // Filtros
                                    filterAvailable?.addEventListener('input', () => applyFilter(filterAvailable, listAvailable));
                                    filterSelected?.addEventListener('input', () => applyFilter(filterSelected, listSelected));

                                    // Botones
                                    btnAddAll?.addEventListener('click', () => moveAll(listAvailable, listSelected, true));
                                    btnAdd?.addEventListener('click', () => moveSelected(listAvailable, listSelected, true));
                                    btnRemove?.addEventListener('click', () => moveSelected(listSelected, listAvailable, false));
                                    btnRemoveAll?.addEventListener('click', () => moveAll(listSelected, listAvailable, false));

                                    // Doble clic para mover rápidamente
                                    listAvailable?.addEventListener('dblclick', (e) => {
                                        const li = e.target.closest('.list-group-item');
                                        if (li) moveOne(li, listSelected, true);
                                    });
                                    listSelected?.addEventListener('dblclick', (e) => {
                                        const li = e.target.closest('.list-group-item');
                                        if (li) moveOne(li, listAvailable, false);
                                    });
                                });
                            </script>
                        @endpush
                    </div>
                </div>
                @endcan

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
