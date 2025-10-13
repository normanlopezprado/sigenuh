<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Collect;
use App\Models\Hospital;
use App\Models\HospitalFloorService;
use App\Support\MealWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CollectCardsController extends Controller
{
    /**
     * Renderiza el nuevo front (cards.blade.php) con:
     * - Servicios disponibles (por hospital)
     * - Camas del servicio seleccionado (si aplica)
     * - Ventana/tiempo de comida activo (o el que venga por query)
     * - Prefill de collects existentes para esa fecha+comida
     */
    public function index(Request $request)
    {
        // 1) Hospital actual (ajústalo si tienes helper auth()->user()->hospital_selected)
        $user = $request->user();
        $hospitalId = $user->hospital_selected;

        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning','Selecciona un hospital.');
        }
        
        $hospital = Hospital::findOrFail($hospitalId);


        // 2) Fecha (por defecto hoy)
        $dateStr = $request->input('date');
        try {
            $date = $dateStr ? Carbon::parse($dateStr)->toDateString() : Carbon::now()->toDateString();
        } catch (\Throwable $e) {
            $date = Carbon::now()->toDateString();
        }

        $hospital = Hospital::find($hospitalId);

        

        
        // 1) Define la TZ local (usa la de tu app o fuerza Guatemala)
// === Ventanas desde hospitals (usa fecha seleccionada + hora actual) ===
$tz  = config('app.timezone', 'America/Guatemala');

// Hora actual en TZ, pero pegada a la fecha seleccionada
$now = Carbon::now($tz);
$now = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} ".$now->format('H:i:s'), $tz);

// Acepta "HH:MM", "HH:MM:SS" o "YYYY-MM-DD HH:MM(:SS)"
$mkDateTime = function ($t) use ($date, $tz) {
    if ($t === null) return null;
    $t = trim((string)$t);
    if ($t === '') return null;

    // Si ya viene con fecha
    if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $t)) {
        try { return Carbon::parse($t, $tz); } catch (\Throwable $e) { return null; }
    }

    // Solo hora
    if (preg_match('/^\d{1,2}:\d{2}$/', $t)) $t .= ':00';
    try {
        return Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$t}", $tz);
    } catch (\Throwable $e) {
        try { return Carbon::parse("{$date} {$t}", $tz); } catch (\Throwable $e2) { return null; }
    }
};

// ¿now está dentro de [start, end]? (maneja cruces de medianoche)
$inWindow = function (Carbon $now, ?Carbon $start, ?Carbon $end) {
    if (!$start || !$end) return false;
    if ($end->lessThanOrEqualTo($start)) { // cruza medianoche
        $end = $end->copy()->addDay();
        if ($now->lessThan($start)) $now = $now->copy()->addDay();
    }
    return $now->betweenIncluded($start, $end);
};

// formateo 12h
$fmt12 = function (?Carbon $dt) {
    return $dt ? $dt->format('g:i a') : null;
};

// Construye ventanas desde hospitals (pueden venir como time o datetime)
$Bstart = $mkDateTime($hospital->breakfast_collection_start);
$Bend   = $mkDateTime($hospital->breakfast_collection_end);
$Lstart = $mkDateTime($hospital->lunch_collection_start);
$Lend   = $mkDateTime($hospital->lunch_collection_end);
$Dstart = $mkDateTime($hospital->dinner_collection_start);
$Dend   = $mkDateTime($hospital->dinner_collection_end);

$windows = [
    'Desayuno' => ['start' => $Bstart, 'end' => $Bend],
    'Almuerzo' => ['start' => $Lstart, 'end' => $Lend],
    'Cena'     => ['start' => $Dstart, 'end' => $Dend],
];

// Detecta activa
$active = null;
foreach ($windows as $label => $w) {
    if ($inWindow($now, $w['start'], $w['end'])) { $active = $label; break; }
}

// Próxima (si no hay activa)
$nextLabel = null; $etaText = null;
if (!$active) {
    $valid = collect($windows)
        ->filter(fn($w) => $w['start'] && $w['end'])
        ->map(fn($w, $label) => ['label'=>$label, 'start'=>$w['start']])
        ->sortBy('start')
        ->values();

    if ($valid->isNotEmpty()) {
        $next = $valid->first(fn($x) => $x['start']->greaterThanOrEqualTo($now))
            ?? ['label'=>$valid->first()['label'], 'start'=>$valid->first()['start']->copy()->addDay()];
        $nextLabel = $next['label'];
        $mins = $now->diffInMinutes($next['start'], false);
        if     ($mins <= 0) $etaText = 'en breve';
        elseif ($mins < 60) $etaText = "en {$mins} min";
        else { $h=intdiv($mins,60); $m=$mins%60; $etaText = $m ? "en {$h} h {$m} min" : "en {$h} h"; }
    }
}

// Título y habilitado
$meal   = $active ?? ($requestedMeal ?? 'Desayuno');
$isOpen = (bool)$active;

$windowMessage = $active
    ? "Ventana activa: {$active}"
    : ($nextLabel ? "Siguiente: {$nextLabel} {$etaText}" : "Fuera de ventanas configuradas");

// Horarios a la derecha del título (solo horas)
$windowConfig = [
    'Desayuno' => ($Bstart && $Bend) ? ($fmt12($Bstart).' - '.$fmt12($Bend)) : null,
    'Almuerzo' => ($Lstart && $Lend) ? ($fmt12($Lstart).' - '.$fmt12($Lend)) : null,
    'Cena'     => ($Dstart && $Dend) ? ($fmt12($Dstart).' - '.$fmt12($Dend)) : null,
];


            


        // 4) Servicios disponibles (HospitalFloorService) para el hospital
        
        $services = HospitalFloorService::query()
        ->with([
            'hospitalFloor.nivel',
            'service',
        ])
        ->whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
        ->get()
        ->map(function ($hfs) {
            $nivelName = $hfs->hospitalFloor?->nivel?->name; // ej. "5to", "4to"
            $floorNum = 0;
            if (is_string($nivelName) && preg_match('/\d+/', $nivelName, $m)) {
                $floorNum = (int)$m[0];
            } else {
                $floorNum = -1; // sin número (PB, etc.)
            }

            $abbr = $hfs->service?->abbreviation;
            $name = $hfs->service?->name;
            $cat  = $hfs->service?->category;

            $label = trim(collect([$nivelName, $abbr, "{$name} {$cat}"])
                ->filter()
                ->implode(' - '));

            return [
                'id'     => $hfs->id,
                'label'  => $label,
                '_floor' => $floorNum,
                '_name'  => strtolower($name ?? ''), // para orden alfabético
            ];
        })
        ->sortBy([
            ['_floor', 'desc'],   // primero ordena por piso descendente
            ['_name', 'asc'],     // luego por nombre del servicio ascendente
        ])
        ->values()
        ->map(fn($row) => [
            'id'    => $row['id'],
            'label' => $row['label'],
        ]);


        // 5) Servicio seleccionado
        $hfsId = $request->input('service'); // en tu select usas name="service"
        $beds = collect();
        $prefill = []; // bed_id => collect (si existe)

        if ($hfsId) {
            $beds = Bed::query()
                ->with([
                    'hospitalFloorService.service',
                    'hospitalFloorService.hospitalFloor.nivel',
                ])
                ->where('hospital_floor_service_id', $hfsId)
                ->orderBy('code')
                ->get();

            if ($beds->isNotEmpty()) {
                // 6) Traer collects existentes para prellenar la UI (fecha + comida)
                $collects = Collect::query()
                    ->whereIn('bed_id', $beds->pluck('id')->all())
                    ->whereDate('date', $date)
                    ->where('meal', $meal)
                    ->get()
                    ->keyBy('bed_id');

                $prefill = $collects->map(fn($c) => [
                    'id'                   => $c->id,
                    'diet_type'            => $c->diet_type,
                    'trays_count'          => $c->trays_count,
                    'disposables_count'    => $c->disposables_count,
                    'notes'                => $c->notes,
                    'has_minor'            => (bool)$c->has_minor,
                    'minor_age'            => $c->minor_age,          
                    'has_companion'        => (bool)$c->has_companion,
                    'companion_diet_type'  => $c->companion_diet_type,
                    'companion_notes'      => $c->companion_notes,
                    'has_disponsable'      => (bool)$c->has_disponsable, 
                ])->toArray();

            }
        }

        // Fallback: si windowConfig quedó vacío, formatea directo desde hospitals.*
        if (empty(array_filter($windowConfig))) {
            $to12 = function ($t) {
                if (!$t) return null;
                try { return \Illuminate\Support\Carbon::parse($t)->format('g:i a'); }
                catch (\Throwable $e) { return null; }
            };
            $windowConfig = [
                'Desayuno' => ($hospital?->breakfast_collection_start && $hospital?->breakfast_collection_end)
                    ? ($to12($hospital->breakfast_collection_start).' - '.$to12($hospital->breakfast_collection_end))
                    : null,
                'Almuerzo' => ($hospital?->lunch_collection_start && $hospital?->lunch_collection_end)
                    ? ($to12($hospital->lunch_collection_start).' - '.$to12($hospital->lunch_collection_end))
                    : null,
                'Cena'     => ($hospital?->dinner_collection_start && $hospital?->dinner_collection_end)
                    ? ($to12($hospital->dinner_collection_start).' - '.$to12($hospital->dinner_collection_end))
                    : null,
            ];
        }


        return view('collects.cards', [
            'hospitalId' => $hospitalId,
            'services'   => $services,
            'selectedService' => $hfsId,
            'date'       => $date,
            'meal'       => $meal,
            'beds'       => $beds,
            'prefill'    => $prefill, 
            'meal'          => $meal,
            'isOpen'        => $isOpen,
            'windowMessage' => $windowMessage,
            'windowConfig'    => $windowConfig,
            
            

        ]);
    }

    /**
     * Cambia el estado de una cama (Disponible/Ocupada).
     * Ruta esperada por tu front: PATCH /collects/bed/{bed}/toggle
     */
    public function toggleBed(Request $request, Bed $bed)
    {
        $toBusy = (int) ($request->input('to_busy') ?? 0); 
        $status = $toBusy ? 'Ocupada' : 'Disponible';

        $bed->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'status' => $status]);
    }

    /**
     * Crea/Actualiza la captura (collect) para una cama en la fecha+comida activa.
     * Úsalo con un POST por cama: /collects/bed/{bed}
     */
    public function upsert(Request $request, Bed $bed)
    {
        // Fecha/Comida: coherentes con la pantalla
        $dateStr = $request->input('date');
        try {
            $date = $dateStr ? Carbon::parse($dateStr)->toDateString() : Carbon::now()->toDateString();
        } catch (\Throwable $e) {
            $date = Carbon::now()->toDateString();
        }

        $meal = $request->input('meal') ?: 'Desayuno';



        // Validación mínima (ajústala a tus enums/reglas reales)
        $data = $request->validate([
            'diet_type'           => ['nullable','string','max:100'],
            'trays_count'         => ['nullable','integer','min:0'],
            'disposables_count'   => ['nullable','integer','min:0'],
            'notes'               => ['nullable','string','max:2000'],
            'has_minor'           => ['nullable','boolean'],
            'has_companion'       => ['nullable','boolean'],
            'companion_diet_type' => ['nullable','string','max:100'],
            'companion_notes'     => ['nullable','string','max:2000'],
        ]);

        // Defaults seguros
        $data['trays_count']       = $data['trays_count']       ?? 0;
        $data['disposables_count'] = $data['disposables_count'] ?? 0;
        $data['has_minor']         = (int)($data['has_minor']   ?? 0);
        $data['has_companion']     = (int)($data['has_companion'] ?? 0);

        $collect = Collect::updateOrCreate(
            [
                'bed_id' => $bed->id,
                'date'   => $date,
                'meal'   => $meal,
            ],
            [
                'diet_type'            => $data['diet_type'] ?? null,
                'trays_count'          => $data['trays_count'],
                'disposables_count'    => $data['disposables_count'],
                'notes'                => $data['notes'] ?? null,
                'has_minor'            => $data['has_minor'],
                'has_companion'        => $data['has_companion'],
                'companion_diet_type'  => $data['companion_diet_type'] ?? null,
                'companion_notes'      => $data['companion_notes'] ?? null,
                'user_id'              => optional(auth()->user())->id, // si tienes user
            ]
        );

        return response()->json([
            'ok'                    => true,
            'collect_id'            => $collect->id,
            'has_minor'             => (bool)$collect->has_minor,
            'has_companion'         => (bool)$collect->has_companion,
            'companion_diet_type'   => $collect->companion_diet_type,
        ]);
    }
    public function bulk(Request $request)
{
    // 1) Normaliza fecha y comida
    $dateStr = $request->input('date');
    try {
        $date = $dateStr ? \Illuminate\Support\Carbon::parse($dateStr)->toDateString() : now()->toDateString();
    } catch (\Throwable $e) {
        $date = now()->toDateString();
    }
    $meal = $request->input('meal') ?: 'Desayuno';


    // 2) Filas enviadas: rows[bed_id][...]
    $rows = $request->input('rows', []);
    if (!is_array($rows) || empty($rows)) {
        return back()->with('warning', 'No se recibieron datos para guardar.');
    }

    $userId = optional(auth()->user())->id;
    $okCount = 0; $failBeds = [];

    \DB::beginTransaction();
    try {
        foreach ($rows as $bedId => $payload) {
            // Solo procesa filas presentes (el hidden __present de la vista)
            if (empty($payload['__present'])) {
                continue;
            }

            // Verifica que exista la cama
            $bed = \App\Models\Bed::find($bedId);
            if (!$bed) {
                $failBeds[] = $bedId;
                continue;
            }

            // 3) Normaliza/valida campo por campo (tolerante)
            $dietType           = $payload['diet_type']            ?? null;
            $traysCount         = isset($payload['trays_count'])         ? (int)$payload['trays_count']         : 0;
            $disposablesCount   = isset($payload['disposables_count'])   ? (int)$payload['disposables_count']   : 0;
            $notes              = $payload['notes']                ?? null;

            $hasDisponsable     = !empty($payload['has_disponsable']) ? 1 : 0; 
            $hasMinor           = !empty($payload['has_minor'])       ? 1 : 0;
            $minorAge           = isset($payload['minor_age'])        ? (int)$payload['minor_age'] : null;

            $hasCompanion       = !empty($payload['has_companion'])   ? 1 : 0;
            $companionDietType  = $payload['companion_diet_type']     ?? null;
            $companionNotes     = $payload['companion_notes']         ?? null;

            // 4) Upsert por (bed_id, date, meal)
            \App\Models\Collect::updateOrCreate(
                [
                    'bed_id' => $bed->id,
                    'date'   => $date,
                    'meal'   => $meal,
                ],
                [
                    'diet_type'            => $dietType,
                    'trays_count'          => $traysCount,
                    'disposables_count'    => $disposablesCount,
                    'notes'                => $notes,
                    'has_disponsable'      => $hasDisponsable,      
                    'has_minor'            => $hasMinor,
                    'minor_age'            => $minorAge,
                    'has_companion'        => $hasCompanion,
                    'companion_diet_type'  => $companionDietType,
                    'companion_notes'      => $companionNotes,
                    'user_id'              => $userId,
                ]
            );

            $okCount++;
        }

        \DB::commit();
    } catch (\Throwable $e) {
        \DB::rollBack();
        return back()->with('error', 'No se pudieron guardar los datos: '.$e->getMessage());
    }

    // Mensaje final
    if ($okCount === 0) {
        return back()->with('warning', 'No se guardó ninguna fila.')->withInput();
    }

    $msg = "Guardado correcto: {$okCount} fila(s)";
    if (!empty($failBeds)) {
        $msg .= '. Algunas camas no se encontraron.';
    }

    // Mantén el servicio seleccionado si vino en el request
    $params = [];
    if ($request->filled('service')) {
        $params['service'] = $request->input('service');
    }

    return redirect()
        ->route('collects.cards', $params)
        ->with('success', $msg);
}

}
