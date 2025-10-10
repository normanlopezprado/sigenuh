@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Servicios -> Camas')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')

    <div class="row g-4">
        <div class="col-12">
            <div class="card mb-0 h-100">
                <div class="container py-4">
                    <h1 class="mb-3">Recolección por camas</h1>

                    {{-- Mensajes --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                            </ul>
                        </div>
                    @endif


                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Servicio</label>
                            <div class="d-flex gap-2">
                                <select name="service" class="form-select" required>
                                    <option value="">— Selecciona un servicio —</option>
                                    @foreach($services as $svc)
                                        <option value="{{ $svc->id }}" @selected($serviceId===$svc->id)>{{ $svc->name }} - {{ $svc->category }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary">Cargar</button>
                            </div>
                        </div>


                        @isset($date)
                            <div class="col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="text" class="form-control" value="{{ \Illuminate\Support\Str::of($date)->toString() }}" readonly>
                            </div>
                        @endisset
                        @isset($meal)
                            <div class="col-md-3">
                                <label class="form-label">Comida</label>
                                <input type="text" class="form-control" value="{{ $meal }}" readonly>
                            </div>
                        @endisset
                    </form>


                    @if(empty($serviceId))
                        <div class="alert alert-info">Selecciona un servicio para ver sus camas y capturar datos.</div>
                        @return
                    @endif


                    <form method="POST" action="{{ route('collects.bulk') }}" id="bulkForm">
                        @csrf

                        <input type="hidden" name="date" value="{{ $date ?? now()->toDateString() }}">
                        <input type="hidden" name="meal" value="{{ $meal ?? 'Desayuno' }}">
                        @if(!empty($prefillSource))
                            <div class="alert alert-info mb-3">
                                No había datos para <strong>{{ $date }} ({{ $meal }})</strong>.
                                Se precargaron valores desde <strong>{{ $prefillSource['date'] }} ({{ $prefillSource['meal'] }})</strong>.
                                Al guardar, se registrarán para la fecha y comida actuales.
                            </div>
                        @endif
                        @if($meal === 'Fuera de rango')
                            <div class="alert alert-warning">
                                Ahora mismo no hay ventana activa de recolección.
                            </div>
                        @elseif(!$isOpen)
                            <div class="alert alert-warning">
                                Aún no está abierta la ventana de recolección para <strong>{{ $meal }}</strong>.
                            </div>
                        @else
                            <div class="alert alert-success">
                                Ventana activa: <strong>{{ $meal }}</strong>. Puedes registrar datos.
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                <tr>
                                    <th style="white-space: nowrap;">Cama</th>
                                    <th style="white-space: nowrap;">Estado cama</th>
                                    <th class="text-center" style="white-space: nowrap;">Menor/Acomp.</th>

                                    @foreach($diets as $d)
                                        <th class="text-center">{{ $d }}</th>
                                    @endforeach
                                    <th class="text-center" style="min-width:120px;">Desechable</th>
                                    <th>Notas</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($beds as $bed)
                                    @php
                                        $col = $collectsByBed[$bed->id] ?? null;
                                        $diet = $col?->diet_type;
                                        $disp  = (bool)($col?->has_disponsable ?? false);
                                        $notes = $col?->notes ?? '';
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $bed->code }}</strong>
                                            @if($bed->hospitalFloorService?->service?->name)
                                                <div class="small text-muted">{{ $bed->hospitalFloorService->service->name }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm {{ $bed->status==='Ocupada' ? 'btn-danger' : 'btn-success' }}"
                                                    data-bed="{{ $bed->id }}"
                                                    onclick="toggleBedStatus(this)">
                                                {{ $bed->status ?? 'Disponible' }}
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $hasMinor = (bool)($col->has_minor ?? false);
                                                $hasComp  = (bool)($col->has_companion ?? false);
                                                $compDiet = $col->companion_diet_type ?? null;
                                            @endphp

                                            @if($hasComp)
                                                <span class="badge bg-info mb-1">Acompañante: {{ $compDiet ?? '—' }}</span><br>
                                            @endif
                                            @if($hasMinor)
                                                <span class="badge bg-secondary mb-1">Menor</span><br>
                                            @endif

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="openCompanionModal('{{ $bed->id }}', {{ json_encode([
                                    'has_minor' => $hasMinor,
                                    'has_companion' => $hasComp,
                                    'companion_diet_type' => $compDiet,
                                  ]) }})">
                                                Configurar
                                            </button>
                                        </td>
                                        {{-- Radios de dieta --}}
                                        @foreach($diets as $d)
                                            <td class="text-center">
                                                <input type="radio"
                                                       name="rows[{{ $bed->id }}][diet_type]"
                                                       value="{{ $d }}"
                                                    @checked($diet===$d)>
                                            </td>
                                        @endforeach

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="rows[{{ $bed->id }}][has_disponsable]"
                                                   value="1"
                                                @checked($disp)>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                   name="rows[{{ $bed->id }}][notes]"
                                                   value="{{ $notes }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 2 + count($diets) + 2 }}" class="text-center text-muted">
                                            No hay camas para este servicio.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" {{ (!$isOpen) ? 'disabled' : '' }}>Guardar</button>
                            <a href="{{ route('collects.index', ['service'=>$serviceId]) }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>


                {{-- Modal Menor/Acompañante --}}
                <div class="modal fade" id="companionModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Menor y acompañante</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <form id="companionForm">
                                    @csrf
                                    <input type="hidden" id="comp-bed-id">
                                    <input type="hidden" id="comp-date" value="{{ $date ?? now()->toDateString() }}">
                                    <input type="hidden" id="comp-meal" value="{{ $meal ?? 'Desayuno' }}">

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="comp-has-minor">
                                        <label class="form-check-label" for="comp-has-minor">Paciente es menor</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="comp-has-companion">
                                        <label class="form-check-label" for="comp-has-companion">¿Tiene acompañante?</label>
                                    </div>

                                    <div id="companionDietWrapper" class="mb-3" style="display:none;">
                                        <label class="form-label">Dieta para acompañante</label>
                                        <select id="comp-diet" class="form-select">
                                            <option value="">— Selecciona una dieta —</option>
                                            @foreach($diets as $d)
                                                <option value="{{ $d }}">{{ $d }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Notas de acompañante (opcional)</label>
                                        <textarea id="comp-notes" class="form-control" rows="2"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button class="btn btn-primary" id="comp-save-btn" {{ (!$isOpen) ? 'disabled' : '' }}>Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>
    <script>
        window.toggleBedStatus = async function toggleBedStatus(btn) {
            const bedId = btn.getAttribute('data-bed');
            if (!bedId) return;
            try {
                const res = await fetch(`{{ url('/collects/bed') }}/${bedId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                if (json && json.ok) {
                    btn.textContent = json.status;
                    btn.classList.toggle('btn-success', json.status === 'Disponible');
                    btn.classList.toggle('btn-danger', json.status === 'Ocupada');
                }
            } catch (e) {
                console.error(e);
                alert('No se pudo cambiar el estado de la cama.');
            }
        }
        let bootstrapModal;

        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('companionModal');
            bootstrapModal = new bootstrap.Modal(modalEl, { keyboard: false });

            const hasCompanion = document.getElementById('comp-has-companion');
            const dietWrapper  = document.getElementById('companionDietWrapper');
            hasCompanion.addEventListener('change', () => {
                dietWrapper.style.display = hasCompanion.checked ? 'block' : 'none';
            });

            document.getElementById('comp-save-btn').addEventListener('click', saveCompanion);
        });

        window.openCompanionModal = function openCompanionModal(bedId, preset) {
            document.getElementById('comp-bed-id').value = bedId;

            const hasMinorEl = document.getElementById('comp-has-minor');
            const hasCompEl  = document.getElementById('comp-has-companion');
            const dietEl     = document.getElementById('comp-diet');

            hasMinorEl.checked = !!(preset?.has_minor);
            hasCompEl.checked  = !!(preset?.has_companion);
            dietEl.value       = preset?.companion_diet_type || '';

            document.getElementById('companionDietWrapper').style.display = hasCompEl.checked ? 'block' : 'none';

            bootstrapModal.show();
        }

        async function saveCompanion() {
            const bedId  = document.getElementById('comp-bed-id').value;
            const date   = document.getElementById('comp-date').value;
            const meal   = document.getElementById('comp-meal').value;

            const hasMinor = document.getElementById('comp-has-minor').checked ? 1 : 0;
            const hasComp  = document.getElementById('comp-has-companion').checked ? 1 : 0;
            const diet     = document.getElementById('comp-diet').value || null;
            const notes    = document.getElementById('comp-notes').value || null;

            try {
                const res = await fetch(`{{ url('/collects/bed') }}/${bedId}/companion`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type':'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        date: date,
                        meal: meal,
                        has_minor: hasMinor,
                        has_companion: hasComp,
                        companion_diet_type: diet,
                        companion_notes: notes,
                    }),
                });

                if (!res.ok) {
                    const txt = await res.text();
                    throw new Error(txt || ('HTTP '+res.status));
                }


                bootstrapModal.hide();
                window.location.href = window.location.href;
            } catch (e) {
                console.error(e);
                alert('No se pudieron guardar los datos del acompañante.');
            }
        }
    </script>
    @include('partials.social-share-modal')

@endsection

@section('js')

    <!-- Datatable js -->

    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>


    <!-- Datatable init -->
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

@endsection



