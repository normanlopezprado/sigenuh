@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Hospitales -> Carritos -> Asignar servicios a carritos')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Asignar servicios a carritos</h5>
                </div>

                <div class="card-body">

                    {{-- Selector de Carrito --}}
                    <form method="GET" class="row g-2 mb-3" onsubmit="return false;">
                        <div class="col-12">
                            <select name="cart" class="form-select"
                                    onchange="if(this.value){ window.location.href = this.value; }">
                                @foreach($carts as $c)
                                    <option value="{{ route('carts.route.edit', $c) }}"
                                        {{ $cart->id === $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} - {{ $c->code_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <hr>

                    <form method="POST" action="{{ route('carts.route.update', $cart) }}" id="routeForm">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            {{-- Servicios Disponibles (HFS no asignados a ningún carrito) --}}
                            <div class="col-12 col-md-5">
                                <input type="text" id="filterAvailable" class="form-control mb-2"
                                       placeholder="Filtrar disponibles...">

                                <div class="card">
                                    <div class="card-header py-2"><strong>Servicios disponibles</strong></div>
                                    <div class="card-body p-0">
                                        <ul id="listAvailable" class="list-group list-group-flush"
                                            style="height: 320px; overflow:auto;">
                                            @foreach($available as $hfs)
                                                @php
                                                    $nivel = $hfs->hospitalFloor->nivel->name ?? 'Nivel';
                                                    $abbr  = $hfs->service->abbreviation ?? '';
                                                    $sname = $hfs->service->name ?? 'Servicio';
                                                    $label = trim($nivel . ' - ' . $abbr . ' - ' . $sname);
                                                @endphp
                                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                                    data-id="{{ $hfs->id }}"
                                                    data-text="{{ strtolower($label) }}">
                                                    <span class="me-2">{{ $label }}</span>
                                                    <i class="ri-arrow-right-line"></i>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="col-12 col-md-2 d-flex flex-column align-items-stretch justify-content-center gap-2">
                                <button type="button" id="btnAdd" class="btn btn-info">Añadir</button>
                                <button type="button" id="btnRemove" class="btn btn-warning">Quitar</button>
                            </div>

                            {{-- Servicios Asignados (HFS del carrito actual) --}}
                            <div class="col-12 col-md-5">
                                <input type="text" id="filterSelected" class="form-control mb-2"
                                       placeholder="Filtrar asignados...">

                                <div class="card">
                                    <div class="card-header py-2"><strong>Servicios asignados</strong></div>
                                    <div class="card-body p-0">
                                        <ul id="listSelected" class="list-group list-group-flush"
                                            style="height: 320px; overflow:auto;">
                                            @foreach($selected as $hfs)
                                                @php
                                                    $nivel = $hfs->hospitalFloor->nivel->name ?? 'Nivel';
                                                    $abbr  = $hfs->service->abbreviation ?? '';
                                                    $sname = $hfs->service->name ?? 'Servicio';
                                                    $label = trim($nivel . ' - ' . $abbr . ' - ' . $sname);
                                                @endphp
                                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                                    data-id="{{ $hfs->id }}"
                                                    data-text="{{ strtolower($label) }}">
                                                    <span class="me-2">{{ $label }}</span>
                                                    <i class="ri-arrow-left-line"></i>
                                                    <input type="hidden" name="selected[]" value="{{ $hfs->id }}">
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <button class="btn btn-primary">Guardar</button>
                            <a href="{{ route('carts.index') }}" class="btn btn-danger">Cancelar</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const listAvailable = document.getElementById('listAvailable');
    const listSelected  = document.getElementById('listSelected');

    function move(li, toList) {
        li.classList.remove('active');
        if (toList.id === 'listSelected') {
            li.querySelector('i')?.remove();
            li.insertAdjacentHTML('beforeend', '<i class="ri-arrow-left-line"></i>');
            if (!li.querySelector('input')) {
                li.insertAdjacentHTML('beforeend', `<input type="hidden" name="selected[]" value="${li.dataset.id}">`);
            }
        } else {
            li.querySelector('i')?.remove();
            li.insertAdjacentHTML('beforeend', '<i class="ri-arrow-right-line"></i>');
            li.querySelector('input')?.remove();
        }
        toList.appendChild(li);
    }

    function filter(input, list) {
        const q = (input.value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        list.querySelectorAll('li').forEach(li => {
            const txt = (li.dataset.text || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            li.classList.toggle('d-none', !txt.includes(q));
        });
    }

    [listAvailable, listSelected].forEach(list => {
        list.addEventListener('click', e => {
            const li = e.target.closest('li'); if (!li) return;
            li.classList.toggle('active');
        });
    
        list.addEventListener('dblclick', e => {
            const li = e.target.closest('li'); if (!li) return;
            move(li, list.id === 'listAvailable' ? listSelected : listAvailable);
        });
    });

    document.getElementById('btnAdd').onclick = () => {
        [...listAvailable.querySelectorAll('.active')].forEach(li => move(li, listSelected));
    };
    document.getElementById('btnRemove').onclick = () => {
        [...listSelected.querySelectorAll('.active')].forEach(li => move(li, listAvailable));
    };

    document.getElementById('filterAvailable').oninput = e => filter(e.target, listAvailable);
    document.getElementById('filterSelected').oninput  = e => filter(e.target, listSelected);
});
</script>
@endsection
