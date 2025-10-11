{{-- resources/views/collects/cards.blade.php --}}
@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Hospitales -> Servicios -> Camas')
@section('pagetitle', 'Recolección por camas (tarjetas)')
@section('buttonTitle', 'Share')

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">

  <style>
    /* ====== GRID ====== */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 12px;
    }
    @media (min-width: 1200px) { .cards-grid > .card-col { grid-column: span 4; } }
    @media (min-width: 768px) and (max-width:1199.98px) { .cards-grid > .card-col { grid-column: span 6; } }
    @media (max-width: 767.98px) { .cards-grid > .card-col { grid-column: span 12; } }

    /* ====== CARD ====== */
    .bed-card { border-radius: 16px; border: 1px solid var(--bs-border-color, #e5e7eb); }
    .bed-card .card-header { background: var(--bs-body-bg, #fff); border-bottom: 1px dashed var(--bs-border-color, #e5e7eb); }

    /* ====== TITULOS ====== */
    .svc-title { font-weight: 800; font-size: 1rem; line-height: 1.25rem; }
    .bed-subtitle { font-size: .95rem; color: #374151; font-weight: 400; } /* normal */

    /* ====== DIET CHIPS ====== */
    .diet-chips, .companion-chips {
      display: grid;
      grid-auto-rows: minmax(36px, auto);
      grid-template-columns: repeat(2, minmax(0,1fr));
      gap: 8px;
    }
    @media (min-width: 576px) { .diet-chips, .companion-chips { grid-template-columns: repeat(3, minmax(0,1fr)); } }
    @media (min-width: 1400px) { .diet-chips, .companion-chips { grid-template-columns: repeat(4, minmax(0,1fr)); } }
    .diet-chip, .companion-chip {
      display: flex; align-items: center; gap: 8px; padding: 8px 10px;
      border: 1px solid var(--bs-border-color, #e5e7eb); border-radius: 999px;
      cursor: pointer; user-select: none; transition: background-color .15s, border-color .15s, box-shadow .15s;
    }
    .diet-chip input[type="radio"], .companion-chip input[type="radio"] { margin: 0; }
    .diet-chip.active, .companion-chip.active { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96,165,250,.2); background: #f8fafc; }

    /* ====== SWITCH DISPONIBLE/OCUPADA ====== */
    .availability { display: flex; align-items: center; gap: .5rem; }
    .availability .state-label { font-size: .85rem; font-weight: 600; min-width: 92px; text-align: right; }
    .availability .form-switch .form-check-input {
      width: 3.1em; height: 1.6em;
      background-size: 1.2em 1.2em;
      cursor: pointer; transition: background-color .15s, border-color .15s, box-shadow .15s;
    }
    .availability .form-check-input { background-color: #f59e0b; border-color: #f59e0b; }
    .availability .form-check-input:checked { background-color: #22c55e; border-color: #22c55e; }
    .state-pill { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
    .state-pill.free  { color: #065f46; background: #d1fae5; }
    .state-pill.busy  { color: #7c2d12; background: #ffedd5; }

    .section-title { font-weight: 700; font-size: .9rem; margin-bottom: .35rem; color: #111827; }

    .floating-actions { position: sticky; bottom: 0; z-index: 20; background: rgba(255,255,255,.9); backdrop-filter: blur(6px); border-top: 1px solid var(--bs-border-color, #e5e7eb); padding: .75rem; display: none; }
    @media (max-width: 767.98px) { .floating-actions { display: block; } }
  </style>
@endsection

@section('content')
  <div class="row g-4">
    <div class="col-12">
      <div class="card mb-0 h-100">
        <div class="container py-4">
         @php
            use Carbon\Carbon;
            use Illuminate\Support\Facades\App;

            App::setLocale('es');
            Carbon::setLocale('es');

            $fechaCarbon = Carbon::parse($date ?? now());
            $fechaFormateada = ucfirst($fechaCarbon->translatedFormat('l d \\d\\e F'));
            $ventanaActiva = $meal ?? 'Sin definir';
        @endphp

        <h1 class="mb-1">
            Recolección de: <span class="text-primary">{{ $ventanaActiva }}</span>
        </h1>
        <small class="text-muted d-block mb-3">
            {{ $fechaFormateada }}
        </small>

          {{-- Mensajes --}}
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
          @endif

          {{-- Filtro SOLO de servicio (auto-submit al cambiar) --}}
          <form id="filterForm" method="GET" action="{{ route('collects.cards') }}" class="row g-2 mb-2">
            <div class="col-12 col-md-8 col-lg-6">
              <label class="form-label">Servicio</label>
              <select name="service" id="serviceSelect" class="form-select" required
                      onchange="document.getElementById('filterForm').submit()">
                <option value="">— Selecciona un servicio —</option>
                @foreach($services as $svc)
                  <option value="{{ $svc->id }}" @selected($serviceId===$svc->id)>
                    {{ !empty($svc->display_levels) ? ($svc->display_levels.' — ') : '' }}
                    {{ !empty($svc->abbreviation) ? ($svc->abbreviation.' — ') : '' }}
                    {{ trim(($svc->name ?? '').' '.($svc->category ?? '')) }}
                  </option>
                @endforeach
              </select>
              <small class="text-muted d-block mt-1">
                Selecciona un servicio para ver sus camas y capturar datos.
              </small>
            </div>

            <noscript>
              <div class="col-12"><button class="btn btn-primary mt-2">Cargar</button></div>
            </noscript>
          </form>

          @if(!empty($prefillSource))
            <div class="alert alert-info mb-3">
                No había datos para <strong>{{ $date }} ({{ $meal }})</strong>.
                Se precargaron valores desde <strong>{{ $prefillSource['date'] }} ({{ $prefillSource['meal'] }})</strong>.
                Al guardar, se registrarán para la fecha y comida actuales.
            </div>
            @endif

          @if(($meal ?? '') === 'Fuera de rango')
            <div class="alert alert-warning">Ahora mismo no hay ventana activa de recolección.</div>
          @elseif(!$isOpen)
            <div class="alert alert-warning">Aún no está abierta la ventana de recolección para <strong>{{ $meal }}</strong>.</div>
          @else
            <div class="alert alert-success">Puedes registrar datos.</div>
          @endif

          <form method="POST" action="{{ route('collects.bulk') }}" id="cardsBulkForm">
            @csrf
            <input type="hidden" name="date" value="{{ $date ?? now()->toDateString() }}">
            <input type="hidden" name="meal" value="{{ $meal ?? 'Desayuno' }}">

            <div class="cards-grid mt-3">
              @forelse($beds as $bed)
                @php
                  $col    = $collectsByBed[$bed->id] ?? null;
                  $diet   = $col['diet_type'] ?? null;
                  $disp   = (bool)($col['has_disponsable'] ?? false);
                  $status = $bed->status ?? 'Disponible';

                  $svc    = $bed->hospitalFloorService?->service;
                  $svcTitle = trim(
                    ($svc?->abbreviation ? ($svc->abbreviation.' - ') : '') .
                    trim(($svc?->name ?? '').' '.($svc?->category ?? ''))
                  );

                  $hasMinor = (bool)($col['has_minor'] ?? false);
                  $minorAge = $col['minor_age'] ?? null;
                  $hasComp  = (bool)($col['has_companion'] ?? false);
                  $compDiet = $col['companion_diet_type'] ?? null;
                @endphp

                <div class="card-col">
                  <div class="card bed-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-start">
                      <div>
                        <div class="svc-title">
                          {{ $svcTitle }}
                        </div>
                        <div class="bed-subtitle">Cama {{ $bed->code }}</div>
                      </div>

                      @php
                        $isAvailable = ($status !== 'Ocupada');
                        $stateText   = $isAvailable ? 'Disponible' : 'Ocupada';
                      @endphp
                      <div class="availability">
                        <span class="state-label">
                          <span class="state-pill {{ $isAvailable ? 'free' : 'busy' }}">{{ $stateText }}</span>
                        </span>
                        <div class="form-check form-switch m-0">
                          <input
                            class="form-check-input availability-input"
                            type="checkbox"
                            role="switch"
                            id="avail-{{ $bed->id }}"
                            data-bed="{{ $bed->id }}"
                            aria-label="Cambiar disponibilidad de la cama {{ $bed->code }}"
                            {{ $isAvailable ? 'checked' : '' }}>
                        </div>
                      </div>
                    </div>

                    <div class="card-body">
                      <div class="mb-3">
                        <label class="form-label">Dieta</label>
                        <div class="diet-chips" data-bed="{{ $bed->id }}">
                          @foreach($diets as $d)
                            @php $checked = $diet===$d; @endphp
                            <label class="diet-chip {{ $checked ? 'active':'' }}">
                              <input type="radio"
                                     name="rows[{{ $bed->id }}][diet_type]"
                                     value="{{ $d }}"
                                     @checked($checked)
                                     aria-label="Cama {{ $bed->code }}, dieta {{ $d }}">
                              <span>{{ $d }}</span>
                            </label>
                          @endforeach
                        </div>
                      </div>

                      <div class="mb-3 form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="disp-{{ $bed->id }}"
                               name="rows[{{ $bed->id }}][has_disponsable]"
                               value="1"
                               @checked($disp)>
                        <label class="form-check-label" for="disp-{{ $bed->id }}">Desechable</label>
                      </div>

                      <div class="mb-2">
                        <div class="form-check form-switch">
                          <input class="form-check-input minor-switch"
                                 type="checkbox"
                                 id="minor-{{ $bed->id }}"
                                 name="rows[{{ $bed->id }}][has_minor]"
                                 value="1"
                                 data-target="#minor-age-{{ $bed->id }}"
                                 @checked($hasMinor)>
                          <label class="form-check-label" for="minor-{{ $bed->id }}">Menor</label>
                        </div>
                      </div>
                      <div class="mb-3" id="minor-age-{{ $bed->id }}" style="{{ $hasMinor ? '' : 'display:none;' }}">
                        <label class="form-label">Edad</label>
                        <input type="number"
                               min="0" max="120" step="1"
                               class="form-control"
                               name="rows[{{ $bed->id }}][minor_age]"
                               value="{{ $minorAge ?? '' }}"
                               placeholder="Edad en años">
                      </div>

                      <div class="mb-2">
                        <div class="form-check form-switch">
                          <input class="form-check-input companion-switch"
                                 type="checkbox"
                                 id="companion-{{ $bed->id }}"
                                 name="rows[{{ $bed->id }}][has_companion]"
                                 value="1"
                                 data-target="#companion-wrapper-{{ $bed->id }}"
                                 @checked($hasComp)>
                          <label class="form-check-label" for="companion-{{ $bed->id }}">Acompañante</label>
                        </div>
                      </div>

                      <div class="mb-2" id="companion-wrapper-{{ $bed->id }}" style="{{ $hasComp ? '' : 'display:none;' }}">
                        <div class="section-title">Dieta — Acompañante</div>
                        <div class="companion-chips" data-bed="{{ $bed->id }}">
                          @foreach($diets as $d)
                            @php $cChecked = $compDiet===$d; @endphp
                            <label class="companion-chip {{ $cChecked ? 'active':'' }}">
                              <input type="radio"
                                     name="rows[{{ $bed->id }}][companion_diet_type]"
                                     value="{{ $d }}"
                                     @checked($cChecked)
                                     aria-label="Cama {{ $bed->code }}, dieta acompañante {{ $d }}">
                              <span>{{ $d }}</span>
                            </label>
                          @endforeach
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              @empty
                <div class="col-12">
                  <div class="alert alert-light border text-muted">No hay camas para este servicio.</div>
                </div>
              @endforelse
            </div>

            <div class="d-none d-md-flex gap-2 mt-3">
              <button class="btn btn-primary" {{ (!$isOpen) ? 'disabled' : '' }}>Guardar</button>
              <a href="{{ route('collects.cards', ['service'=>$serviceId]) }}" class="btn btn-secondary">Cancelar</a>
            </div>

            <div class="floating-actions d-md-none">
              <div class="d-flex gap-2">
                <button class="btn btn-primary w-100" {{ (!$isOpen) ? 'disabled' : '' }}>Guardar</button>
                <a href="{{ route('collects.cards', ['service'=>$serviceId]) }}" class="btn btn-outline-secondary">Cancelar</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js')
  <script>
    /* ====== (sigue siendo útil si tu layout sí hace @yield('js')) ====== */
    (function () {
      const form = document.getElementById('filterForm');
      const service = document.getElementById('serviceSelect');

      let submitting = false;
      let debounceTimer = null;

      function submitSafely() {
        if (submitting) return;
        if (!service.value) return;

        submitting = true;
        service.setAttribute('disabled', 'disabled');
        document.body.style.cursor = 'progress';
        form.requestSubmit ? form.requestSubmit() : form.submit();
      }

      function debounceSubmit(delay = 150) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(submitSafely, delay);
      }

      service?.addEventListener('change', () => debounceSubmit(150));

      window.addEventListener('pageshow', () => {
        submitting = false;
        service?.removeAttribute('disabled');
        document.body.style.cursor = '';
      });
    })();

    /* ====== CHIP ACTIVO ====== */
    document.addEventListener('change', (e) => {
      if (e.target.matches('.diet-chips input[type="radio"]')) {
        const group = e.target.closest('.diet-chips');
        group.querySelectorAll('.diet-chip').forEach(ch => ch.classList.remove('active'));
        e.target.closest('.diet-chip').classList.add('active');
      }
      if (e.target.matches('.companion-chips input[type="radio"]')) {
        const group = e.target.closest('.companion-chips');
        group.querySelectorAll('.companion-chip').forEach(ch => ch.classList.remove('active'));
        e.target.closest('.companion-chip').classList.add('active');
      }
    });

    /* ====== SWITCH DISPONIBILIDAD ====== */
    function updateStateUI(container, isAvailable) {
      const pill = container.querySelector('.state-pill');
      if (!pill) return;
      pill.textContent = isAvailable ? 'Disponible' : 'Ocupada';
      pill.classList.toggle('free',  isAvailable);
      pill.classList.toggle('busy', !isAvailable);
    }

    document.addEventListener('change', async (e) => {
      if (e.target.matches('.availability-input')) {
        const input  = e.target;
        const bedId  = input.getAttribute('data-bed');
        const isAvailable = input.checked;
        const parent = input.closest('.availability');
        updateStateUI(parent, isAvailable);
        try {
          const res = await fetch(`{{ url('/collects/bed') }}/${bedId}/toggle`, {
            method: 'PATCH',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ to_busy: isAvailable ? 0 : 1 })
          });
          const json = await res.json();
          if (!json || !json.ok) throw new Error('Respuesta inválida');
          if (typeof json.status === 'string') {
            const availableByServer = (json.status !== 'Ocupada');
            if (availableByServer !== isAvailable) input.checked = availableByServer;
            updateStateUI(parent, input.checked);
          }
        } catch (err) {
          input.checked = !isAvailable;
          updateStateUI(parent, input.checked);
          console.error(err);
          alert('No se pudo cambiar el estado de la cama.');
        }
      }

      if (e.target.matches('.minor-switch')) {
        const targetSel = e.target.getAttribute('data-target');
        const target = document.querySelector(targetSel);
        if (target) target.style.display = e.target.checked ? '' : 'none';
      }

      if (e.target.matches('.companion-switch')) {
        const targetSel = e.target.getAttribute('data-target');
        const target = document.querySelector(targetSel);
        if (target) target.style.display = e.target.checked ? '' : 'none';
      }
    });
  </script>

  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
  <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
