@extends('partials.layouts.master')

@section('title', 'Entrega de Comidas')
@section('sub-title', 'Registro diario')
@section('pagetitle', 'Nutrición')

@section('content')
<div class="container-xxl">
    {{-- CSRF para fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ALERTA --}}
    <div id="alertBox" class="alert d-none" role="alert"></div>

    {{-- CARD: Selección + Búsqueda + Confirmación --}}
    <div class="card mb-3">
        <div class="card-body">
            {{-- 1) Selección de comida + menú (solo dietas libres) --}}
            <h5 class="mb-3">1) Selecciona Comida y Menú</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tipo de comida</label>
                    <select class="form-select" id="mealType">
                        <option value="desayuno" {{ ($mealType ?? 'desayuno') === 'desayuno' ? 'selected' : '' }}>Desayuno</option>
                        <option value="almuerzo" {{ ($mealType ?? '') === 'almuerzo' ? 'selected' : '' }}>Almuerzo</option>
                        <option value="cena"     {{ ($mealType ?? '') === 'cena' ? 'selected' : '' }}>Cena</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Menú (dietas libres)</label>
                    <select class="form-select" id="menuId">
                        <option value="">-- Selecciona --</option>
                        @isset($menus)
                            @foreach ($menus as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                    <small class="text-muted">Solo se muestran menús activos de dieta <b>Libre</b>.</small>
                </div>
            </div>

            <hr class="my-4">

            {{-- 2) Búsqueda de beneficiario (autocompletar por nombre) --}}
            <h5 class="mb-3">2) Buscar beneficiario</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-6 position-relative">
                    <label class="form-label">Nombre del beneficiario</label>
                    <input type="text" class="form-control" id="beneficiarySearch" placeholder="Escribe el nombre completo">
                    <input type="hidden" id="beneficiaryId"> {{-- aquí se guarda el ID seleccionado --}}
                    {{-- Contenedor de sugerencias --}}
                    <div id="beneficiarySuggestions" class="list-group position-absolute w-100 mt-1 d-none"
                        style="z-index: 1000; max-height: 240px; overflow-y: auto;">
                        {{-- items dinámicos --}}
                    </div>
                    <small class="text-muted">Escribe al menos 2 letras y selecciona un resultado.</small>
                </div>
            </div>

            <hr class="my-4">

            {{-- 3) Confirmación de entrega --}}
            <h5 class="mb-3">3) Confirmar entrega</h5>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Usuario</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Anotaciones</label>
                    <input type="text" class="form-control" id="notes" placeholder="Opcional">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-50" id="btnConfirm">Confirmar</button>
                    <button class="btn btn-outline-secondary w-50" id="btnReset">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD: Tabla de entregas de HOY por tipo seleccionado --}}
    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">
                4) Entregas de hoy — <span id="mealTypeLabel" class="text-capitalize">{{ $mealType ?? 'desayuno' }}</span>
            </h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Beneficiario</th>
                            <th>Puesto</th>
                            <th>Menú</th>
                            <th>Entregado por</th>
                            <th>Fecha/Hora</th>
                            <th>Anotaciones</th>
                        </tr>
                    </thead>
                    <tbody id="deliveriesTbody">
                        <tr><td colspan="6" class="text-muted">Cargando…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
const routes = {
    menus: "{{ route('staff.meals.menus') }}",
    search: "{{ route('staff.meals.search') }}",
    store: "{{ route('staff.meals.store') }}",
    today: "{{ route('staff.meals.today') }}"
};

function showAlert(type, msg) {
    const box = document.getElementById('alertBox');
    box.className = 'alert alert-' + type;
    box.textContent = msg;
    box.classList.remove('d-none');
    setTimeout(() => box.classList.add('d-none'), 3500);
}

async function fetchJSON(url) {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

async function loadMenus() {
    const mealType = document.getElementById('mealType').value;
    const menuSel = document.getElementById('menuId');
    menuSel.innerHTML = '<option value="">Cargando…</option>';
    try {
        const data = await fetchJSON(`${routes.menus}?meal_type=${encodeURIComponent(mealType)}`);
        menuSel.innerHTML = '<option value="">-- Selecciona --</option>';
        data.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.name;
            menuSel.appendChild(opt);
        });
        document.getElementById('mealTypeLabel').textContent = mealType;
    } catch (e) {
        menuSel.innerHTML = '<option value="">Error cargando menús</option>';
    }
}

/* ===== Autocompletado por nombre ===== */
let searchTimer = null;

function clearSuggestions() {
    const box = document.getElementById('beneficiarySuggestions');
    box.innerHTML = '';
    box.classList.add('d-none');
}

function showSuggestions(items) {
    const box = document.getElementById('beneficiarySuggestions');
    box.innerHTML = '';

    items.forEach(item => {
        const a = document.createElement('a');
        a.href = '#';
        a.className = 'list-group-item list-group-item-action';
        a.textContent = item.name;
        a.dataset.id = item.id;
        a.addEventListener('click', (ev) => {
            ev.preventDefault();
            document.getElementById('beneficiarySearch').value = item.name;
            document.getElementById('beneficiaryId').value = item.id;
            clearSuggestions();
        });
        box.appendChild(a);
    });

    box.classList.toggle('d-none', items.length === 0);
}

async function onBeneficiaryInput() {
    const q = document.getElementById('beneficiarySearch').value.trim();
    document.getElementById('beneficiaryId').value = ''; // reset ID si cambia el texto

    if (q.length < 2) {
        clearSuggestions();
        return;
    }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        try {
            const data = await fetchJSON(`${routes.search}?q=${encodeURIComponent(q)}`);
            if (!data.length) {
                clearSuggestions();
                return;
            }
            showSuggestions(data);
        } catch (e) {
            clearSuggestions();
        }
    }, 250);
}

/* Cerrar sugerencias si hace clic fuera */
document.addEventListener('click', (e) => {
    const box = document.getElementById('beneficiarySuggestions');
    const input = document.getElementById('beneficiarySearch');
    if (!box.contains(e.target) && e.target !== input) {
        clearSuggestions();
    }
});

/* ===================================== */

async function loadTodayTable() {
    const mealType = document.getElementById('mealType').value;
    const tbody = document.getElementById('deliveriesTbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-muted">Cargando…</td></tr>';
    try {
        const data = await fetchJSON(`${routes.today}?meal_type=${encodeURIComponent(mealType)}`);
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-muted">Sin entregas registradas hoy.</td></tr>';
            return;
        }
        tbody.innerHTML = '';
        data.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${r.beneficiary ?? ''}</td>
                <td>${r.position ?? ''}</td>
                <td>${r.menu ?? ''}</td>
                <td>${r.delivered_by ?? ''}</td>
                <td>${r.delivered_at ?? ''}</td>
                <td>${r.notes ?? ''}</td>
            `;
            tbody.appendChild(tr);
        });
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Error cargando entregas.</td></tr>';
    }
}

async function confirmDelivery() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const payload = {
        meal_type: document.getElementById('mealType').value,
        menu_id: document.getElementById('menuId').value,
        beneficiary_id: document.getElementById('beneficiaryId').value, // <- viene del hidden
        notes: document.getElementById('notes').value.trim(),
    };

    if (!payload.menu_id)        return showAlert('warning', 'Selecciona un menú.');
    if (!payload.beneficiary_id) return showAlert('warning', 'Selecciona un beneficiario de la lista.');

    const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
    });

    if (!res.ok) {
        const msg = await res.text();
        return showAlert('danger', 'Error al registrar: ' + msg);
    }
    const data = await res.json();
    if (data.ok) {
        showAlert('success', data.message);
        // limpiar campos
        document.getElementById('notes').value = '';
        document.getElementById('beneficiarySearch').value = '';
        document.getElementById('beneficiaryId').value = '';
        clearSuggestions();
        await loadTodayTable();
    } else {
        showAlert('danger', data.message ?? 'Error desconocido');
    }
}

function resetAll() {
    document.getElementById('menuId').value = '';
    document.getElementById('beneficiarySearch').value = '';
    document.getElementById('beneficiaryId').value = '';
    clearSuggestions();
    document.getElementById('notes').value = '';
}

document.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('mealType').addEventListener('change', async () => {
        await loadMenus();
        await loadTodayTable();
    });
    document.getElementById('beneficiarySearch').addEventListener('input', onBeneficiaryInput);
    document.getElementById('btnConfirm').addEventListener('click', confirmDelivery);
    document.getElementById('btnReset').addEventListener('click', resetAll);

    await loadTodayTable(); // primera carga
});
</script>
@endsection

@push('css')
<style>
#beneficiarySuggestions.list-group > a {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
</style>
@endpush
