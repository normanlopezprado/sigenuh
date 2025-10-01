@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Logística -> Carritos -> Asignar rutas')
@section('pagetitle', 'Rutas de Carritos')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-10">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Asignar servicios a la ruta del carrito</h5>
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

                {{-- Paso 1: Selector de Hospital --}}
                <form method="GET" action="{{ route('carts.routes.index') }}" class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <select name="hospital" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Seleccionar hospital --</option>
                            @foreach($hospitals as $h)
                                <option value="{{ $h->id }}" @selected(optional($selectedHospital)->id === $h->id)>
                                    {{ $h->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Paso 2: Selector de Carrito (se muestra cuando hay hospital) --}}
                    @if($selectedHospital && $carts->count())
                        <div class="col-12 col-md-6">
                            <select name="cart" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Seleccionar carrito --</option>
                                @foreach($carts as $cart)
                                    <option value="{{ $cart->id }}" @selected(optional($selectedCart)->id === $cart->id)>
                                        {{ $cart->name }} - {{ $cart->code_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </form>

                {{-- Paso 3: Dual-list (se muestra cuando hay hospital y carrito) --}}
                @if($selectedHospital && $selectedCart)
                    <form method="POST" action="{{ route('carts.routes.update') }}" id="routeForm">
                        @csrf
                        <input type="hidden" name="hospital" value="{{ $selectedHospital->id }}">
                        <input type="hidden" name="cart" value="{{ $selectedCart->id }}">

                        <div class="row g-3">
                            {{-- Disponibles --}}
                            <div class="col-12 col-md-5">
                                <div class="mb-2">
                                    <input type="text" id="filterAvailable" class="form-control" placeholder="Filtrar disponibles...">
                                </div>
                                <div class="card">
                                    <div class="card-header py-2"><strong>Disponibles</strong></div>
                                    <div class="card-body p-0">
                                        <ul id="listAvailable" class="list-group list-group-flush" style="height: 360px; overflow:auto;">
                                            @forelse($available as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                                    data-id="{{ $item->id }}"
                                                    data-text="{{ strtolower($item->text) }}"
                                                    role="button">
                                                    <span class="me-2">{{ $item->text }}</span>
                                                    <i class="ri-arrow-right-line"></i>
                                                </li>
                                            @empty
                                                <li class="list-group-item text-muted">No hay servicios disponibles.</li>
                                            @endforelse
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

                            {{-- Seleccionados (ruta del carrito) --}}
                            <div class="col-12 col-md-5">
                                <div class="mb-2">
                                    <input type="text" id="filterSelected" class="form-control" placeholder="Filtrar seleccionados...">
                                </div>
                                <div class="card">
                                    <div class="card-header py-2"><strong>Seleccionados</strong></div>
                                    <div class="card-body p-0">
                                        <ul id="listSelected" class="list-group list-group-flush" style="height: 360px; overflow:auto;">
                                            @forelse($selected as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                                    data-id="{{ $item->id }}"
                                                    data-text="{{ strtolower($item->text) }}"
                                                    role="button">
                                                    <span class="me-2">{{ $item->text }}</span>
                                                    <i class="ri-arrow-left-line"></i>
                                                    <input type="hidden" name="services[]" value="{{ $item->id }}">
                                                </li>
                                            @empty
                                                <li class="list-group-item text-muted">No hay servicios asignados aún.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <button class="btn btn-primary">Guardar</button>
                            <a href="{{ route('carts.routes.index') }}" class="btn btn-secondary">Limpiar</a>
                            <a href="{{ route('dashboard') }}" class="btn btn-danger">Cancelar</a>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function toggleActive(li) { li.classList.toggle('active'); }

    function moveOne(li, toEl) {
        li.classList.remove('active');
        if (toEl.id === 'listSelected') {
            li.querySelector('i')?.remove();
            const icon = document.createElement('i');
            icon.className = 'ri-arrow-left-line';
            li.appendChild(icon);

            if (!li.querySelector('input[type="hidden"]')) {
                const hid = document.createElement('input');
                hid.type = 'hidden';
                hid.name = 'services[]';
                hid.value = li.getAttribute('data-id');
                li.appendChild(hid);
            }
        } else {
            li.querySelector('i')?.remove();
            const icon = document.createElement('i');
            icon.className = 'ri-arrow-right-line';
            li.appendChild(icon);
            li.querySelector('input[type="hidden"]')?.remove();
        }
        toEl.appendChild(li);
    }

    function moveSelected(fromEl, toEl) {
        fromEl.querySelectorAll('.list-group-item.active').forEach(li => moveOne(li, toEl));
    }

    function moveAll(fromEl, toEl) {
        fromEl.querySelectorAll('.list-group-item:not(.d-none)').forEach(li => moveOne(li, toEl));
    }

    function applyFilter(inputEl, listEl) {
        const q = (inputEl.value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        listEl.querySelectorAll('.list-group-item').forEach(li => {
            const txt = (li.getAttribute('data-text') || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            li.classList.toggle('d-none', q && !txt.includes(q));
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const listAvailable = document.getElementById('listAvailable');
        const listSelected  = document.getElementById('listSelected');

        const filterAvailable = document.getElementById('filterAvailable');
        const filterSelected  = document.getElementById('filterSelected');

        const btnAddAll = document.getElementById('btnAddAll');
        const btnAdd    = document.getElementById('btnAdd');
        const btnRemove = document.getElementById('btnRemove');
        const btnRemoveAll = document.getElementById('btnRemoveAll');

        [listAvailable, listSelected].forEach(list => {
            list?.addEventListener('click', e => {
                const li = e.target.closest('.list-group-item');
                if (!li) return;
                toggleActive(li);
            });
        });

        filterAvailable?.addEventListener('input', () => applyFilter(filterAvailable, listAvailable));
        filterSelected?.addEventListener('input', () => applyFilter(filterSelected, listSelected));

        btnAddAll?.addEventListener('click', () => moveAll(listAvailable, listSelected));
        btnAdd?.addEventListener('click', () => moveSelected(listAvailable, listSelected));
        btnRemove?.addEventListener('click', () => moveSelected(listSelected, listAvailable));
        btnRemoveAll?.addEventListener('click', () => moveAll(listSelected, listAvailable));

        listAvailable?.addEventListener('dblclick', e => {
            const li = e.target.closest('.list-group-item'); if (li) moveOne(li, listSelected);
        });
        listSelected?.addEventListener('dblclick', e => {
            const li = e.target.closest('.list-group-item'); if (li) moveOne(li, listAvailable);
        });
    });
</script>
@endsection
