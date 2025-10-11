
@php
    $_fecha = $fecha ?? ($date ?? now()->toDateString());

    try {
        $fechaISO = \Carbon\Carbon::parse($_fecha)->toDateString();
    } catch (\Throwable $e) {
        $fechaISO = now()->toDateString();
    }

    $calendars = \App\Models\Calendar::query()
    ->with([
        'menu',
        'menu.menuIngredients.ingredient',
        'optionalMenuIngredients.ingredient',
    ])
    ->whereDate('calendars.date', $fechaISO)
    ->join('menus', 'calendars.menu_id', '=', 'menus.id')
    ->orderByRaw("
        CASE menus.category
            WHEN 'Desayuno' THEN 1
            WHEN 'Almuerzo' THEN 2
            WHEN 'Cena' THEN 3
            ELSE 4
        END
    ")
    ->orderBy('calendars.date')
    ->select('calendars.*')
    ->get();
@endphp

<div class="card">
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
                        <div class="w-100">
                            @php
                                $cat  = $menu?->category ? ucfirst($menu->category) : null;
                                $diet = $menu?->diet_type ?: null;
                                $tituloLinea1 = collect([$cat, $diet])->filter()->implode(' - ');
                            @endphp

                            {{-- Línea 1: Categoría - Dieta (centrado y en negrita) --}}
                            @if($tituloLinea1)
                                <p class="mb-1 text-center fw-bold text-uppercase">
                                    {{ $tituloLinea1 }}
                                </p>
                            @endif

                            {{-- Línea 2: Nombre del menú (negrita alineada a la izquierda) --}}
                            <p class="mb-1 fw-bold text-start">{{ $menu?->name ?? 'Menú sin nombre' }}</p>

                            {{-- Notas (si hay) --}}
                            @if(!empty($cal->notes))
                                <p class="mb-2 text-muted"><em>{{ $cal->notes }}</em></p>
                            @endif
                        </div>
                    </div>

                    @if($ingredientesDelDia->isEmpty())
                        <div class="text-muted small">Sin ingredientes configurados para este menú.</div>
                    @else
                        <ul class="mt-2 mb-0" style="margin-left: 15px;">
                            @foreach($ingredientesDelDia as $mi)
                                @php
                                    $esOpcionalSel = (bool) ($mi->is_optional ?? false);
                                    $qty = $fmtQty($mi->qty ?? null);
                                    $unit = $mi->unit ?? null;
                                @endphp
                                <li>
                                    {{ $mi->ingredient?->name ?? 'Ingrediente' }}
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
