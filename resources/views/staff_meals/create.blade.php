@extends('partials.layouts.master2')

@section('title', 'SIGENUH · Entrega a Personal Autorizado')
@section('sub-title', 'Entrega a Personal Autorizado')
@section('pagetitle', 'SIGENUH')
@section('buttonTitle', 'Ayuda')
@section('modalTarget', 'helpModal')

@section('content')
<div class="container-fluid max-w-6xl mx-auto">

    {{-- Flash / errores --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    {{-- 1) Selección de comida y tipo de dieta --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="ri-restaurant-2-line"></i>
            <strong>1) Seleccionar comida y dieta</strong>
            <span class="ms-auto text-muted small" id="mealHint">Sugerencia: —</span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3" id="mealButtons">
                <button class="btn btn-outline-primary meal-btn" type="button" data-meal="desayuno">
                    <i class="ri-sun-foggy-line"></i> Desayuno
                </button>
                <button class="btn btn-outline-primary meal-btn" type="button" data-meal="almuerzo">
                    <i class="ri-restaurant-line"></i> Almuerzo
                </button>
                <button class="btn btn-outline-primary meal-btn" type="button" data-meal="cena">
                    <i class="ri-moon-clear-line"></i> Cena
                </button>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label mb-1">Tipo de dieta</label>
                    <select id="dietType" class="form-select" disabled>
                        <option value="">— Selecciona una comida primero —</option>
                        @foreach($dietTypes as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- 2) Seleccionar beneficiario --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="ri-team-line"></i>
            <strong>2) Seleccionar beneficiario</strong>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Buscar por nombre / teléfono</label>
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Ej. Juan Pérez o 5555-5555">
                        <button class="btn btn-outline-secondary" type="button" id="btnSearch">
                            <i class="ri-search-line"></i> Buscar
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Resultados</label>
                    <div id="resultsBox" class="border rounded p-2" style="min-height: 96px;">
                        <div class="text-muted" id="noResults">Sin resultados. Escribe para buscar…</div>
                        <div class="d-flex flex-column d-none" id="resultsList"></div>
                    </div>
                </div>
            </div>

            {{-- Beneficiario seleccionado --}}
            <div id="selectedBeneficiary" class="alert alert-info mt-3 d-none">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><i class="ri-user-smile-line"></i> Beneficiario:</strong>
                        <span id="sb_name">—</span> · <span id="sb_job">—</span>
                        <br><strong>Tel:</strong> <span id="sb_phone">—</span>
                    </div>
                    <div><span class="badge bg-primary" id="sb_status">Autorizado</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3) Confirmar y entregar --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="ri-key-2-line"></i>
            <strong>3) Confirmar y entregar</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('staff.meals.store') }}" id="deliverForm">
                @csrf
                <input type="hidden" name="staff_beneficiary_id" id="f_beneficiary_id">
                <input type="hidden" name="meal_type"            id="f_meal_type">
                <input type="hidden" name="diet_type"            id="f_diet_type">
                <input type="hidden" name="notes"                id="f_notes">

                {{-- Fila 1: usuario + confirmar contraseña --}}
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Usuario que entrega</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name ?? 'Operador' }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" name="operator_password" placeholder="Tu contraseña" required>
                    </div>
                </div>

                {{-- Fila 2: Observaciones + botones --}}
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-8">
                        <label class="form-label mb-1">Observaciones</label>
                        <textarea class="form-control" id="observations" rows="2" placeholder="Observaciones de la entrega…"></textarea>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1" id="btnReset">
                            <i class="ri-refresh-line"></i> Reiniciar
                        </button>
                        <button type="submit" class="btn btn-success flex-grow-1" id="btnDeliver" disabled>
                            <i class="ri-check-double-line"></i> Confirmar entrega
                        </button>
                    </div>
                </div>

                <div class="mt-3 small text-muted">
                    Reglas (mockup):
                    <ul class="mb-0">
                        <li>Solo una entrega por tipo de comida al día por beneficiario.</li>
                        <li>Registro de fecha/hora exacta y usuario que entrega.</li>
                    </ul>
                </div>
            </form>
        </div>
    </div>

    {{-- 4) Tabla entregas --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="ri-list-check-2"></i>
            <strong>4) Entregas de <span id="tblMealType">—</span> (hoy)</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Beneficiario</th>
                            <th>Puesto</th>
                            <th>Dieta</th>
                            <th>Entregado por</th>
                        </tr>
                    </thead>
                    <tbody id="deliveriesTbody">
                        {{-- Se llena dinámicamente --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
(function () {
  // ---------- REFS ----------
  const mealBtns        = document.querySelectorAll('.meal-btn');
  const fMeal           = document.getElementById('f_meal_type');
  const dietSelect      = document.getElementById('dietType');
  const fDiet           = document.getElementById('f_diet_type');
  const deliverBtn      = document.getElementById('btnDeliver');
  const tblMealType     = document.getElementById('tblMealType');
  const deliveriesTbody = document.getElementById('deliveriesTbody');

  const sbName    = document.getElementById('sb_name');
  const sbJob     = document.getElementById('sb_job');
  const sbPhone   = document.getElementById('sb_phone');
  const fBenId    = document.getElementById('f_beneficiary_id');
  const selected  = document.getElementById('selectedBeneficiary');

  const noResults   = document.getElementById('noResults');
  const resultsList = document.getElementById('resultsList');
  const searchInput = document.getElementById('searchInput');
  const btnSearch   = document.getElementById('btnSearch');

  const observations = document.getElementById('observations');
  const fNotes       = document.getElementById('f_notes');

  // ---------- MOCK DATA ----------
  const MOCK_BENEFICIARIES = [
    { id:'uuid-1', name:'María López',    phone:'5551-1111', job:'Enfermera' },
    { id:'uuid-2', name:'Carlos Pérez',   phone:'5552-2222', job:'Camillero' },
    { id:'uuid-3', name:'Ana Ruiz',       phone:'5553-3333', job:'Técnico Radiólogo' },
    { id:'uuid-4', name:'José Morales',   phone:'5554-4444', job:'Médico Residente' },
    { id:'uuid-5', name:'Laura Gómez',    phone:'5555-5555', job:'Nutricionista' },
    { id:'uuid-6', name:'Ricardo Herrera',phone:'5556-6666', job:'Personal Limpieza' },
  ];

  const MOCK_DELIVERIES = [
    { time:'07:15', name:'María López',    job:'Enfermera',          diet:'Libre',            user:'Operador 1', meal:'desayuno' },
    { time:'07:20', name:'José Morales',   job:'Médico Residente',   diet:'Diabético 1,500',  user:'Operador 2', meal:'desayuno' },
    { time:'12:05', name:'Carlos Pérez',   job:'Camillero',          diet:'Hiposódica',       user:'Operador 1', meal:'almuerzo' },
    { time:'12:30', name:'Laura Gómez',    job:'Nutricionista',      diet:'Renal',            user:'Operador 1', meal:'almuerzo' },
    { time:'18:45', name:'Ana Ruiz',       job:'Técnico Radiólogo',  diet:'Blanda',           user:'Operador 2', meal:'cena' },
  ];

  // ---------- RENDER TABLA ----------
  function renderDeliveriesForMeal(meal) {
    deliveriesTbody.innerHTML = '';
    if (!meal) { tblMealType.textContent = '—'; return; }
    tblMealType.textContent = meal.charAt(0).toUpperCase() + meal.slice(1);
    const rows = MOCK_DELIVERIES.filter(x => x.meal === meal);
    if (!rows.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="5" class="text-muted">Sin entregas registradas para ${tblMealType.textContent}.</td>`;
      deliveriesTbody.appendChild(tr);
      return;
    }
    rows.forEach(r => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${r.time}</td><td>${r.name}</td><td>${r.job}</td><td>${r.diet}</td><td>${r.user}</td>`;
      deliveriesTbody.appendChild(tr);
    });
  }

  // ---------- BUSCAR BENEFICIARIOS ----------
  function renderResultButton(b) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-light d-flex justify-content-between align-items-center text-start result-item py-2';
    btn.innerHTML = `<span><i class="ri-user-3-line"></i> ${b.name} · ${b.job}</span><span class="text-muted">${b.phone}</span>`;
    btn.addEventListener('click', () => {
      // seleccionar beneficiario
      sbName.textContent  = b.name;
      sbJob.textContent   = b.job;
      sbPhone.textContent = b.phone;
      fBenId.value        = b.id;
      selected.classList.remove('d-none');
      maybeEnableDeliver();
    });
    return btn;
  }

  function doSearch() {
    const q = (searchInput.value || '').trim().toLowerCase();
    resultsList.innerHTML = '';
    if (!q) {
      noResults.textContent = 'Sin resultados. Escribe para buscar…';
      noResults.classList.remove('d-none');
      resultsList.classList.add('d-none');
      return;
    }
    const hits = MOCK_BENEFICIARIES.filter(b => b.name.toLowerCase().includes(q) || b.phone.includes(q));
    if (!hits.length) {
      noResults.textContent = 'Sin resultados para: ' + q;
      noResults.classList.remove('d-none');
      resultsList.classList.add('d-none');
      return;
    }
    noResults.classList.add('d-none');
    resultsList.classList.remove('d-none');
    hits.forEach(h => resultsList.appendChild(renderResultButton(h)));
  }

  btnSearch.addEventListener('click', doSearch);
  searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

  // ---------- SELECCIÓN COMIDA ----------
  const hint = document.getElementById('mealHint');
  if (hint) {
    const now = new Date(), h = now.getHours();
    hint.textContent = 'Sugerencia: ' + ((h>=5&&h<11)?'Desayuno':(h>=11&&h<16)?'Almuerzo':'Cena');
  }

  mealBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      mealBtns.forEach(x => x.classList.remove('active'));
      btn.classList.add('active');
      fMeal.value = btn.dataset.meal;
      dietSelect.disabled = false;
      dietSelect.value = '';
      fDiet.value = '';
      renderDeliveriesForMeal(fMeal.value);
      maybeEnableDeliver();
    });
  });

  dietSelect.addEventListener('change', () => { fDiet.value = dietSelect.value; maybeEnableDeliver(); });

  // ---------- Observaciones -> hidden notes ----------
  observations.addEventListener('input', () => { fNotes.value = observations.value; });

  function maybeEnableDeliver() {
    deliverBtn.disabled = !(fMeal.value && fDiet.value && fBenId.value);
  }

  // ---------- Reiniciar ----------
  document.getElementById('btnReset').addEventListener('click', () => {
    // sección 1
    mealBtns.forEach(x => x.classList.remove('active'));
    fMeal.value = '';
    dietSelect.disabled = true; dietSelect.value = ''; fDiet.value = '';
    // sección 2
    selected.classList.add('d-none'); sbName.textContent = sbJob.textContent = sbPhone.textContent = '—';
    fBenId.value = '';
    noResults.textContent = 'Sin resultados. Escribe para buscar…';
    noResults.classList.remove('d-none');
    resultsList.classList.add('d-none'); resultsList.innerHTML = '';
    searchInput.value = '';
    // sección 3
    observations.value = ''; fNotes.value = '';
    // sección 4
    renderDeliveriesForMeal('');
    // botón
    maybeEnableDeliver();
  });

  // ---------- INIT ----------
  renderDeliveriesForMeal('');
})();
</script>
@endsection
