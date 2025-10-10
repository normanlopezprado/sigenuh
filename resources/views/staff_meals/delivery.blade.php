{{-- resources/views/staff_meals/delivery.blade.php --}}
@extends('partials.layouts.master')

@section('title', '')
@section('sub-title', 'Entrega de alimentos')
@section('pagetitle', 'Entrega de alimentos')

@section('content')

{{-- === SECCIÓN 1: SELECCIÓN INICIAL (category, diet_type, menú del día) === --}}
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Selección inicial</h5>
    <div class="small text-muted mt-1">
      Fecha: <strong>{{ ($today ?? \Carbon\Carbon::today())->format('Y-m-d') }}</strong>
    </div>
  </div>
  <div class="card-body">
    <form id="deliveryForm" autocomplete="off">
      @csrf

      <div class="row g-3 align-items-end">
        {{-- TIEMPO DE COMIDA (menus.category) --}}
        <div class="col-md-4">
          <label for="category" class="form-label">Tiempo de comida</label>
          <select id="category" name="category" class="form-select" required>
            @php
              $fallbackCategories = ['desayuno','almuerzo','cena'];
              $cats = isset($categories) && count($categories) ? $categories : $fallbackCategories;
              $selectedCategory = old('category', $mealType ?? 'desayuno');
            @endphp
            @foreach($cats as $cat)
              <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>
                {{ ucfirst($cat) }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- TIPO DE DIETA (menus.diet_type) --}}
        <div class="col-md-4">
          <label for="diet_type" class="form-label">Tipo de dieta</label>
          <select id="diet_type" name="diet_type" class="form-select" required disabled>
            <option value="">— Selecciona tipo de dieta —</option>
          </select>
        </div>

        {{-- MENÚ DEL DÍA (calendars filtrado por fecha hoy + category + diet_type) --}}
        <div class="col-md-4">
          <label for="calendar_menu_id" class="form-label">Menú del día</label>
          <select id="calendar_menu_id" name="calendar_menu_id" class="form-select" required disabled>
            <option value="">— Selecciona un menú —</option>
          </select>
        </div>
      </div>

      {{-- Resumen rápido de selección --}}
      <div class="mt-3 d-flex gap-2 flex-wrap">
        <span class="badge bg-primary" id="badgeCategory">Categoría: {{ ucfirst($selectedCategory) }}</span>
        <span class="badge bg-secondary" id="badgeDiet">Dieta: —</span>
        <span class="badge bg-info text-dark" id="badgeMenu">Menú: —</span>
      </div>

      {{-- Hidden para usar luego en el registro --}}
      <input type="hidden" id="selected_calendar_id" name="selected_calendar_id" value="">
      <input type="hidden" id="selected_menu_id" name="selected_menu_id" value="">
    </form>
  </div>
</div>

{{-- === SECCIÓN 2: BUSCAR BENEFICIARIO (staff_beneficiaries) === --}}
<div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0">Buscar beneficiario</h5>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="beneficiarySearch" class="form-label">Nombre del beneficiario</label>
        <input type="text" id="beneficiarySearch" class="form-control" placeholder="Escribe al menos 2 letras...">
        <div class="form-text">Busca por <strong>nombre completo</strong> de beneficiarios activos.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Coincidencias</label>
        <ul id="beneficiaryResults" class="list-group" style="max-height: 220px; overflow-y: auto;">
          {{-- Resultados por JS --}}
        </ul>
      </div>
    </div>

    <div class="mt-3">
      <span class="badge bg-success" id="badgeBeneficiary">Beneficiario: —</span>
      <input type="hidden" id="selected_beneficiary_id" value="">
    </div>
  </div>
</div>

{{-- === SECCIÓN 3: REGISTRAR ENTREGA === --}}
<div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0">Registrar entrega</h5>
  </div>
  <div class="card-body">
    <div class="alert alert-secondary" id="summaryBox">
      <div><strong>Fecha:</strong> {{ ($today ?? \Carbon\Carbon::today())->format('Y-m-d') }}</div>
      <div><strong>Tiempo de comida:</strong> <span id="sumCategory">—</span></div>
      <div><strong>Tipo de dieta:</strong> <span id="sumDiet">—</span></div>
      <div><strong>Menú:</strong> <span id="sumMenu">—</span></div>
      <div><strong>Beneficiario:</strong> <span id="sumBeneficiary">—</span></div>
      <div><strong>Entregado por:</strong> <span id="sumUser">{{ auth()->user()->name ?? '—' }}</span></div>
    </div>

    <div class="row g-3 align-items-end mb-2">
      <div class="col-md-4">
        <label class="form-label">Entregado por</label>
        <input type="text" class="form-control" value="{{ auth()->user()->name ?? '' }}" readonly>
      </div>
      <div class="col-md-4">
        <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
        <input type="password" id="confirmPassword" class="form-control" placeholder="Requerido para confirmar">
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="button" id="btnRegister" class="btn btn-primary" disabled>
        <i class="ri-check-line"></i> Registrar entrega
      </button>
      <button type="button" id="btnClear" class="btn btn-outline-secondary">
        <i class="ri-close-line"></i> Limpiar selección
      </button>
    </div>

    <div class="mt-3">
      <div id="feedback" class="small"></div>
    </div>
  </div>
</div>

{{-- === SECCIÓN 4: ENTREGAS DE HOY (según selección de tiempo de comida) === --}}
<div class="card mt-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Entregas de hoy</h5>
    <span class="small text-muted">Filtrado por tiempo de comida seleccionado</span>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th style="width: 36px;">#</th>
            <th>Beneficiario</th>
            <th>Menú</th>
            <th>Hora</th>
            <th>Entregado por</th> {{-- nueva --}}
          </tr>
        </thead>
        <tbody id="deliveriesTbody">
          <tr><td colspan="5" class="text-muted">Sin datos…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
  
  const $category  = document.getElementById('category');
  const $dietType  = document.getElementById('diet_type');
  const $menuSel   = document.getElementById('calendar_menu_id');

  const $badgeCategory = document.getElementById('badgeCategory');
  const $badgeDiet     = document.getElementById('badgeDiet');
  const $badgeMenu     = document.getElementById('badgeMenu');

  const $selectedCalendar = document.getElementById('selected_calendar_id');
  const $selectedMenuId   = document.getElementById('selected_menu_id');

  const $beneficiarySearch  = document.getElementById('beneficiarySearch');
  const $beneficiaryResults = document.getElementById('beneficiaryResults');
  const $badgeBeneficiary   = document.getElementById('badgeBeneficiary');
  const $selectedBenefId    = document.getElementById('selected_beneficiary_id');

  const $sumCategory = document.getElementById('sumCategory');
  const $sumDiet     = document.getElementById('sumDiet');
  const $sumMenu     = document.getElementById('sumMenu');
  const $sumBenef    = document.getElementById('sumBeneficiary');

  const $btnRegister = document.getElementById('btnRegister');
  const $btnClear    = document.getElementById('btnClear');
  const $feedback    = document.getElementById('feedback');

  const $deliveriesTbody = document.getElementById('deliveriesTbody');
  const $confirmPassword = document.getElementById('confirmPassword');


  const TODAY = @json(($today ?? \Carbon\Carbon::today())->format('Y-m-d'));

  const ROUTE_DIET_TYPES     = @json(route('staff_meals.diet-types'));
  const ROUTE_MENUS_TODAY    = @json(route('staff_meals.menus-today'));
  const ROUTE_SEARCH_BENEF   = @json(route('staff_meals.search-beneficiaries'));
  const ROUTE_DELIVER        = @json(route('staff_meals.deliver'));
  const ROUTE_LIST           = @json(route('staff_meals.list-deliveries'));


  function setBadge(el, label, value) {
    el.textContent = `${label}: ${value || '—'}`;
  }
  function resetMenu() {
    $menuSel.innerHTML = '<option value="">— Selecciona un menú —</option>';
    $menuSel.disabled = true;
    setBadge($badgeMenu, 'Menú', '—');
    $selectedCalendar.value = '';
    $selectedMenuId.value = '';
    $sumMenu.textContent = '—';
  }
  function updateSummary() {
    const cat = $category.value;
    const diet = $dietType.value;
    const menuText = $menuSel.options[$menuSel.selectedIndex]?.text || '—';
    const benefText = $badgeBeneficiary.textContent.replace('Beneficiario: ', '') || '—';
    $sumCategory.textContent = cat ? cat.charAt(0).toUpperCase() + cat.slice(1) : '—';
    $sumDiet.textContent     = diet || '—';
    $sumMenu.textContent     = menuText;
    $sumBenef.textContent    = benefText;
    $btnRegister.disabled = !($selectedMenuId.value && $selectedBenefId.value && $confirmPassword.value.length > 0);
  }
  function showFeedback(msg, ok=true) {
    $feedback.className = 'small ' + (ok ? 'text-success' : 'text-danger');
    $feedback.textContent = msg;
  }
  function clearFeedback() {
    $feedback.textContent = '';
    $feedback.className = 'small';
  }

  async function loadDietTypes() {
    const category = $category.value;
    setBadge($badgeCategory, 'Categoría', category ? category.charAt(0).toUpperCase() + category.slice(1) : '—');

    $dietType.innerHTML = '<option value="">Cargando...</option>';
    $dietType.disabled = true;

    try {
      const url = new URL(ROUTE_DIET_TYPES, window.location.origin);
      url.searchParams.set('category', category);

      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const data = await res.json(); 

      $dietType.innerHTML = '<option value="">— Selecciona tipo de dieta —</option>';
      (data || []).forEach(dt => {
        const opt = document.createElement('option');
        opt.value = dt;
        opt.textContent = dt;
        $dietType.appendChild(opt);
      });

      $dietType.disabled = false;
      setBadge($badgeDiet, 'Dieta', '—');
      resetMenu();
      updateSummary();
      loadDeliveries(); 
    } catch (e) {
      console.error(e);
      $dietType.innerHTML = '<option value="">No se pudieron cargar tipos</option>';
    }
  }

  async function loadMenusToday() {
    const category = $category.value;
    const dietType = $dietType.value;

    setBadge($badgeDiet, 'Dieta', dietType || '—');

    if (!category || !dietType) {
      resetMenu();
      updateSummary();
      return;
    }

    $menuSel.innerHTML = '<option value="">Cargando...</option>';
    $menuSel.disabled = true;

    try {
      const url = new URL(ROUTE_MENUS_TODAY, window.location.origin);
      url.searchParams.set('category', category);
      url.searchParams.set('diet_type', dietType);
      url.searchParams.set('date', TODAY);

      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const data = await res.json();

      $menuSel.innerHTML = '<option value="">— Selecciona un menú —</option>';
      (data || []).forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;                
        opt.textContent = item.text;
        opt.dataset.menuId = item.menu_id;  
        $menuSel.appendChild(opt);
      });

      $menuSel.disabled = false;
      setBadge($badgeMenu, 'Menú', '—');
      $selectedCalendar.value = '';
      $selectedMenuId.value = '';
      updateSummary();
    } catch (e) {
      console.error(e);
      $menuSel.innerHTML = '<option value="">No se pudieron cargar menús</option>';
      setBadge($badgeMenu, 'Menú', '—');
      $selectedCalendar.value = '';
      $selectedMenuId.value = '';
      updateSummary();
    }
  }

  $category.addEventListener('change', () => { loadDietTypes(); clearFeedback(); });
  $dietType.addEventListener('change', () => { loadMenusToday(); clearFeedback(); });
  $menuSel?.addEventListener('change', () => {
    const text = $menuSel.options[$menuSel.selectedIndex]?.text || '—';
    const menuId = $menuSel.options[$menuSel.selectedIndex]?.dataset?.menuId || '';
    setBadge($badgeMenu, 'Menú', text);
    $selectedCalendar.value = $menuSel.value || '';
    $selectedMenuId.value   = menuId || '';
    updateSummary();
    clearFeedback();
  });
  
  loadDietTypes();

  let beneficiaryTimeout = null;

  $beneficiarySearch.addEventListener('keyup', function () {
    const q = this.value.trim();
    if (q.length < 2) {
      $beneficiaryResults.innerHTML = '';
      setBadge($badgeBeneficiary, 'Beneficiario', '—');
      $selectedBenefId.value = '';
      updateSummary();
      return;
    }
    clearTimeout(beneficiaryTimeout);
    beneficiaryTimeout = setTimeout(() => searchBeneficiaries(q), 300);
  });

  async function searchBeneficiaries(query) {
    try {
      const url = new URL(ROUTE_SEARCH_BENEF, window.location.origin);
      url.searchParams.set('q', query);
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const data = await res.json(); 

      $beneficiaryResults.innerHTML = '';
      if (!data || !data.length) {
        $beneficiaryResults.innerHTML = '<li class="list-group-item text-muted">Sin resultados</li>';
        return;
      }
      data.forEach(item => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action';
        li.textContent = item.full_name;
        li.title = 'Seleccionar beneficiario';
        li.dataset.id = item.id;
        li.addEventListener('click', () => selectBeneficiary(item));
        $beneficiaryResults.appendChild(li);
      });
    } catch (e) {
      console.error(e);
    }
  }

  function selectBeneficiary(item) {
    setBadge($badgeBeneficiary, 'Beneficiario', item.full_name);
    $selectedBenefId.value = item.id;
    updateSummary();
    clearFeedback();
  }

  
  document.getElementById('confirmPassword').addEventListener('input', updateSummary);

  $btnRegister.addEventListener('click', async function () {
    clearFeedback();

    const benefId  = $selectedBenefId.value;
    const mealType = $category.value;       
    const date     = TODAY;                 
    const menuId   = $selectedMenuId.value; 
    const pwd      = $confirmPassword.value;

    if (!mealType)  { showFeedback('Selecciona el tiempo de comida.', false); return; }
    if (!menuId)    { showFeedback('Selecciona el menú del día.', false); return; }
    if (!benefId)   { showFeedback('Selecciona un beneficiario.', false); return; }
    if (!pwd)       { showFeedback('Ingresa tu contraseña para confirmar.', false); return; }

    const token = document.querySelector('input[name="_token"]')?.value || '';
    $btnRegister.disabled = true;

    try {
      const res = await fetch(ROUTE_DELIVER, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({
          staff_beneficiary_id: benefId,
          meal_type: mealType,
          delivery_date: date,
          menu_id: menuId,
          confirm_password: pwd, 
        })
      });

      const data = await res.json();

      if (!res.ok) {
        showFeedback(data?.message || 'No se pudo registrar la entrega.', false);
      } else {
        showFeedback(data?.message || 'Entrega registrada con éxito.', true);
        
        $confirmPassword.value = '';
        updateSummary();
        
        await loadDeliveries();
      }
    } catch (e) {
      console.error(e);
      showFeedback('Error de red al registrar la entrega.', false);
    } finally {
      $btnRegister.disabled = false;
    }
  });

  $btnClear.addEventListener('click', () => {
    $dietType.value = '';
    resetMenu();
    setBadge($badgeDiet, 'Dieta', '—');

    setBadge($badgeBeneficiary, 'Beneficiario', '—');
    $selectedBenefId.value = '';
    $beneficiarySearch.value = '';
    $beneficiaryResults.innerHTML = '';

    document.getElementById('confirmPassword').value = '';

    updateSummary();
    clearFeedback();
    loadDeliveries();
  });

  
  async function loadDeliveries() {
    const mealType = $category.value || 'desayuno';
    const url = new URL(ROUTE_LIST, window.location.origin);
    url.searchParams.set('date', TODAY);
    url.searchParams.set('meal_type', mealType);

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const data = await res.json(); 
      $deliveriesTbody.innerHTML = '';

      if (!data || !data.length) {
        $deliveriesTbody.innerHTML = '<tr><td colspan="5" class="text-muted">Sin datos…</td></tr>';
        return;
      }

      data.forEach((row, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${idx + 1}</td>
          <td>${row.beneficiary}</td>
          <td>${row.menu_name}</td>
          <td>${row.delivered_at}</td>
          <td>${row.delivered_by_name}</td>
        `;
        $deliveriesTbody.appendChild(tr);
      });
    } catch (e) {
      console.error(e);
      $deliveriesTbody.innerHTML = '<tr><td colspan="5" class="text-danger">Error al cargar</td></tr>';
    }
  }

  document.addEventListener('DOMContentLoaded', loadDeliveries);
})();
</script>
@endpush
