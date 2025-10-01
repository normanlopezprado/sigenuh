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

    {{-- Filtro: solo Servicio --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-6">
            <label class="form-label">Servicio</label>
            <div class="d-flex gap-2">
                <select name="service" class="form-select" required>
                    <option value="">— Selecciona un servicio —</option>
                    @foreach($services as $svc)
                        <option value="{{ $svc->id }}" @selected($serviceId===$svc->id)>{{ $svc->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Cargar</button>
            </div>
        </div>

        {{-- Mostrar info contextual (solo lectura) si el controlador la envía --}}
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

    {{-- Si no hay servicio seleccionado aún, mostramos aviso y salimos --}}
    @if(empty($serviceId))
        <div class="alert alert-info">Selecciona un servicio para ver sus camas y capturar datos.</div>
        @return
    @endif

    {{-- Form de guardado masivo --}}
    <form method="POST" action="{{ route('collects.bulk') }}" id="bulkForm">
        @csrf
        {{-- Fecha y Meal ocultos (definidos por el controlador) --}}
        <input type="hidden" name="date" value="{{ $date ?? now()->toDateString() }}">
        <input type="hidden" name="meal" value="{{ $meal ?? 'Desayuno' }}">
        @if(!empty($prefillSource))
            <div class="alert alert-info mb-3">
                No había datos para <strong>{{ $date }} ({{ $meal }})</strong>.
                Se precargaron valores desde <strong>{{ $prefillSource['date'] }} ({{ $prefillSource['meal'] }})</strong>.
                Al guardar, se registrarán para la fecha y comida actuales.
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th style="white-space: nowrap;">Cama</th>
                    <th style="white-space: nowrap;">Estado cama</th>
                    {{-- Cabeceras de dietas como radios --}}
                    @foreach($diets as $d)
                        <th class="text-center">{{ $d }}</th>
                    @endforeach
                    <th class="text-center" style="min-width:120px;">Bandejas</th>
                    <th class="text-center" style="min-width:140px;">Desechables</th>
                    <th>Notas</th>
                </tr>
                </thead>
                <tbody>
                @forelse($beds as $bed)
                    @php
                        $col = $collectsByBed[$bed->id] ?? null;
                        $diet = $col?->diet_type;
                        $trays = $col?->trays_count ?? 0;
                        $disp  = $col?->disposables_count ?? 0;
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
                            <input type="number" min="0" step="1"
                                   class="form-control form-control-sm text-center"
                                   name="rows[{{ $bed->id }}][trays]"
                                   value="{{ $trays }}">
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" step="1"
                                   class="form-control form-control-sm text-center"
                                   name="rows[{{ $bed->id }}][disposables]"
                                   value="{{ $disp }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                   name="rows[{{ $bed->id }}][notes]"
                                   value="{{ $notes }}">
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + count($diets) + 3 }}" class="text-center text-muted">No hay camas para este servicio.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('collects.index', ['service'=>$serviceId]) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
    async function toggleBedStatus(btn) {
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
</script>
