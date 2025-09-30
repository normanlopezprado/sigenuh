
@php
    $_fecha = $fecha ?? ($date ?? now()->toDateString());

    try {
        $fechaISO = \Carbon\Carbon::parse($_fecha)->toDateString();
    } catch (\Throwable $e) {
        $fechaISO = now()->toDateString();
    }

    $calendars = \App\Models\Calendar::with([
        'menu',
        'menu.menuIngredients.ingredient',
        'optionalMenuIngredients.ingredient',
    ])
    ->whereDate('date', $fechaISO)
    ->orderBy('date')
    ->get();
@endphp

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Menús del día — {{ \Carbon\Carbon::parse($fechaISO)->locale('es')->isoFormat('dddd D [de] MMMM YYYY') }}</h5>
        <a href="{{ route('calendars.create', ['date' => $fechaISO]) }}" class="btn btn-sm btn-primary">➕ Crear menú</a>
    </div>

    <div class="card-body">
        @if($calendars->isEmpty())
            <div class="alert alert-info mb-0">
                No hay menús asignados para esta fecha.
            </div>
        @else
            <div class="list-group">
                @foreach($calendars as $cal)
                    @php
                        $menu = $cal->menu;

                        // Ingredientes obligatorios del menú (is_optional = false)
                        $obligatorios = collect();
                        if ($menu && $menu->relationLoaded('menuIngredients')) {
                          $obligatorios = $menu->menuIngredients->where('is_optional', false);
                        }

                        // Opcionales seleccionados en este calendario
                        $opcSeleccionados = $cal->optionalMenuIngredients ?? collect();

                        // Unir ambos conjuntos y evitar duplicados por ID
                        $ingredientesDelDia = $obligatorios->concat($opcSeleccionados)->unique('id');

                        // Helper para formatear cantidad (si existe) sin ceros a la derecha
                        $fmtQty = function ($qty) {
                          if ($qty === null) return null;
                          $s = rtrim(rtrim(number_format((float)$qty, 3, '.', ''), '0'), '.');
                          return $s === '' ? '0' : $s;
                        };
                    @endphp

                    <div class="list-group-item">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h6 class="mb-1">
                                    {{ $menu?->name ?? 'Menú sin nombre' }}
                                    @if($menu?->category)
                                        <small class="text-muted"> · {{ ucfirst($menu->category) }}</small>
                                    @endif
                                    @if($menu?->diet_type)
                                        <small class="text-muted"> · {{ $menu->diet_type }}</small>
                                    @endif
                                </h6>
                                @if(!empty($cal->notes))
                                    <p class="mb-2 text-muted"><em>{{ $cal->notes }}</em></p>
                                @endif
                            </div>
                            <div class="ms-3">
                                <a href="{{ route('calendars.edit', $cal->id) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                            </div>
                        </div>

                        {{-- Ingredientes --}}
                        @if($ingredientesDelDia->isEmpty())
                            <div class="text-muted small">Sin ingredientes configurados para este menú.</div>
                        @else
                            <ul class="mt-2 mb-0">
                                @foreach($ingredientesDelDia as $mi)
                                    @php
                                        // Determinar si este ingrediente es opcional seleccionado
                                        $esOpcionalSel = (bool) ($mi->is_optional ?? false);
                                        // Cuando viene desde $opcSeleccionados, seguro es opcional
                                        // Si viene desde obligatorios, is_optional es false.
                                        // qty / unit pueden existir en menu_ingredient
                                        $qty = $fmtQty($mi->qty ?? null);
                                        $unit = $mi->unit ?? null;
                                    @endphp
                                    <li>
                                        <strong>{{ $mi->ingredient?->name ?? 'Ingrediente' }}</strong>
                                        @if($qty)
                                            — {{ $qty }}@if($unit) {{ ' ' . $unit }}@endif
                                        @endif
                                        @if($esOpcionalSel)
                                            <span class="badge bg-warning text-dark ms-2">Opcional seleccionado</span>
                                        @endif
                                        @if(!empty($mi->notes))
                                            <br><small class="text-muted">{{ $mi->notes }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
