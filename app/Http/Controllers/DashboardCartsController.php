<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\HospitalFloor;
use App\Models\HospitalFloorService;
use App\Models\Nivel;
use App\Models\Service;
use App\Models\Hospital;
use App\Models\Cart;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;



class DashboardCartsController extends Controller
{
    /**
     * Renderiza el dashboard de carritos.
     * GET /dashboard/carts
     */
    public function index(HttpRequest $request)
    {
        $user = Auth::user();

        $hospitals = Hospital::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $selectedHospital = $this->resolveSelectedHospital($request, $user);
        $liveDataUrl = route('dashboard.carts.live');
        $headerVars = $this->defaultHeaderWindowVars($selectedHospital);

        return view('dashboard-carts', [
            'hospitals'          => $hospitals,
            'hospital'           => $selectedHospital,
            'liveDataUrl'        => $liveDataUrl,
            'noHospitalSelected' => $selectedHospital === null,
            'dietTypes'          => $this->loadDietTypesFromDb(),
        ] + $headerVars);
    }

    
    public function live(HttpRequest $request)
    {

        $validated = $request->validate([
            'hospital_id' => ['required', 'uuid'],
        ]);
        $hospitalId = $validated['hospital_id'];

        $hospital = Hospital::where('id', $hospitalId)
            ->where('status', true)
            ->firstOrFail();

        $win  = $this->computeWindowFromDb($hospital);
        $date = $win['date'] ?? now()->toDateString();
        $meal = $win['meal'] ?? null;

        // Tablas (por si cambian en modelos)
        $cartsTable    = (new Cart)->getTable();                   // carts
        $pivotTable    = 'cart_service';
        $hfsTable      = (new HospitalFloorService)->getTable();   // hospital_floor_services
        $hfTable       = (new HospitalFloor)->getTable();          // hospital_floors
        $nivelsTable   = (new Nivel)->getTable();                  // nivels
        $servicesTable = (new Service)->getTable();                // services
        $bedsTable     = 'beds';                                   // según tu esquema

        try {
            // 1) Carts del hospital
            $carts = DB::table("$cartsTable as c")
                ->join("$pivotTable as cs", 'cs.cart_id', '=', 'c.id')
                ->join("$hfsTable as hfs", 'hfs.id', '=', 'cs.hospital_floor_service_id')
                ->join("$hfTable as hf", 'hf.id', '=', 'hfs.hospital_floor_id')
                ->where('hf.hospital_id', $hospitalId)
                ->groupBy('c.id','c.name','c.code_name','c.color', DB::raw('`c`.`order`'), 'c.status','c.notes')
                ->select(
                    'c.id','c.name','c.code_name','c.color',
                    DB::raw('`c`.`order` as `order`'),
                    'c.status','c.notes',
                    DB::raw('COUNT(DISTINCT hfs.id) as services_count_for_hospital')
                )
                ->orderByRaw('`c`.`order` IS NULL, `c`.`order` ASC')
                ->orderBy('c.name')
                ->get();

            if ($carts->isEmpty()) {
                $payload = []; // Initialize $payload as an empty array
                return response()->json([
                    'carts'         => $payload,
                    'active_window' => $win['label'] ?? null,
                    'window'        => $win,
                    'window_key'    => $win['key'] ?? null, 
                    'server_time'   => now()->toIso8601String(),
                ]);
            }

            $cartIds = $carts->pluck('id');

            // 2) Servicios por carrito — "Nivel — Servicio Categoria"
            // Detecta columna categoría
            $serviceCategoryCol = Schema::hasColumn($servicesTable, 'categoria')
            ? 'categoria'
            : (Schema::hasColumn($servicesTable, 'category') ? 'category' : null);

        $pathsRows = DB::table("$pivotTable as cs")
            ->join("$hfsTable as hfs", 'hfs.id', '=', 'cs.hospital_floor_service_id')
            ->join("$hfTable as hf", 'hf.id', '=', 'hfs.hospital_floor_id')
            ->leftJoin("$nivelsTable as n", 'n.id', '=', 'hf.nivel_id')
            ->leftJoin("$servicesTable as s", 's.id', '=', 'hfs.service_id')
            ->where('hf.hospital_id', $hospitalId)
            ->whereIn('cs.cart_id', $cartIds)
            ->select([
                'cs.cart_id',
                DB::raw('COALESCE(n.name, "") as nivel'),
                DB::raw('COALESCE(s.name, "") as servicio'),
                // Si tu MariaDB/MySQL tiene REGEXP_REPLACE: perfecto.
                DB::raw("CAST(REGEXP_REPLACE(COALESCE(n.name, ''), '[^0-9]', '') AS UNSIGNED) as floor_order"),
                // Asegura alias 'categoria' exista siempre
                $serviceCategoryCol
                    ? DB::raw("COALESCE(s.`{$serviceCategoryCol}`, '') as categoria")
                    : DB::raw("'' as categoria"),
            ])
            ->orderBy('floor_order', 'desc')   // piso DESC
            ->orderBy('servicio', 'asc')       // servicio ASC (usa el alias)
            ->orderBy('categoria', 'asc')      // categoría ASC (alias siempre existente)
            ->get();

        $pathsByCart = $pathsRows->groupBy('cart_id')->map(function ($rows) {
            return collect($rows)->map(function ($r) {
                // NO leer directamente $r->nivel/$r->servicio/$r->categoria sin checar
                $nivel     = property_exists($r, 'nivel')     ? trim((string)$r->nivel)     : '';
                $servicio  = property_exists($r, 'servicio')  ? trim((string)$r->servicio)  : '';
                $categoria = property_exists($r, 'categoria') ? trim((string)$r->categoria) : '';

                // "Traumatología Mujeres" (sin doble espacios si no hay categoría)
                $servicioCat = trim($servicio . ' ' . $categoria);

                // "4to — Traumatología Mujeres"
                return collect([ $nivel !== '' ? $nivel : null, $servicioCat !== '' ? $servicioCat : null ])
                    ->filter()
                    ->implode(' — ');
            })->filter()->values()->all();
        });

            // 3) Contadores (regla exclusiva: principal Bandeja/O Desechable; acompañante solo Bandeja)
            // 3) Contadores (forzados por ventana)
            $date = $win['date'] ?? now()->toDateString();
            $meal = $win['meal'] ?? null;


            // Principal: si has_disponsable=1 → Desechable, si no → Bandeja
            $mainQ = DB::table('collects as col')
                ->join("$bedsTable as b", 'b.id', '=', 'col.bed_id')
                ->join("$hfsTable as hfs", 'hfs.id', '=', 'b.hospital_floor_service_id')
                ->join("$hfTable as hf", 'hf.id', '=', 'hfs.hospital_floor_id')
                ->join("$pivotTable as cs", 'cs.hospital_floor_service_id', '=', 'hfs.id')
                ->where('hf.hospital_id', $hospitalId)
                ->whereIn('cs.cart_id', $cartIds)
                ->whereDate('col.date', $date)
                ->when($meal, fn($q) => $q->where('col.meal', $meal))
                ->whereNotNull('col.diet_type')
                ->groupBy('cs.cart_id', 'col.diet_type')
                ->selectRaw("
                    cs.cart_id,
                    col.diet_type as diet_type,
                    SUM(CASE WHEN col.has_disponsable = 1 THEN 0 ELSE 1 END) as b_count,
                    SUM(CASE WHEN col.has_disponsable = 1 THEN 1 ELSE 0 END) as d_count
                ");

            // Acompañante: siempre Bandeja=1; Desechable=0
            $compQ = DB::table('collects as col')
                ->join("$bedsTable as b", 'b.id', '=', 'col.bed_id')
                ->join("$hfsTable as hfs", 'hfs.id', '=', 'b.hospital_floor_service_id')
                ->join("$hfTable as hf", 'hf.id', '=', 'hfs.hospital_floor_id')
                ->join("$pivotTable as cs", 'cs.hospital_floor_service_id', '=', 'hfs.id')
                ->where('hf.hospital_id', $hospitalId)
                ->whereIn('cs.cart_id', $cartIds)
                ->whereDate('col.date', $date)
                ->when($meal, fn($q) => $q->where('col.meal', $meal))
                ->where('col.has_companion', 1)
                ->whereNotNull('col.companion_diet_type')
                ->groupBy('cs.cart_id', 'col.companion_diet_type')
                ->selectRaw("
                    cs.cart_id,
                    col.companion_diet_type as diet_type,
                    COUNT(*) as b_count,
                    0 as d_count
                ");

            $countsRows = DB::query()
                ->fromSub($mainQ->unionAll($compQ), 'u')
                ->selectRaw("cart_id, diet_type, SUM(b_count) as bandeja, SUM(d_count) as desechable")
                ->groupBy('cart_id', 'diet_type')
                ->get();

            $countsByCart = $countsRows->groupBy('cart_id')->map(function ($rows) {
                $map = [];
                foreach ($rows as $r) {
                    $b = (int) $r->bandeja;
                    $d = (int) $r->desechable;
                    $map[$r->diet_type] = [
                        'bandeja'    => $b,
                        'desechable' => $d,
                        'total'      => $b + $d,
                    ];
                }
                return $map;
            });

            // 4) Payload final
            $payload = $carts->map(function ($c) use ($pathsByCart, $countsByCart) {
                return [
                    'id'             => $c->id,
                    'name'           => $c->name,
                    'code_name'      => $c->code_name,
                    'color'          => $c->color,
                    'order'          => $c->order,
                    'status'         => (bool) $c->status,
                    'notes'          => $c->notes,
                    'services_count' => (int) ($c->services_count_for_hospital ?? 0),
                    'service_paths'  => $pathsByCart->get($c->id, []),
                    'counts'         => $countsByCart->get($c->id, []),
                ];
            });

            return response()->json([
                'carts'         => $payload,
                'active_window' => $this->computeActiveWindowLabel($hospital),
                'window'        => $this->computeWindowFromDb($hospital), // ← ventana con reset_at
                'window_key'    => optional($this->computeWindowFromDb($hospital))['key'] ?? null,
                'server_time'   => now()->toIso8601String(),
            ]);


        } catch (\Throwable $e) {
            if ($request->boolean('debug')) {
                return response()->json([
                    'error'   => 'query_failed',
                    'message' => $e->getMessage(),
                ], 500);
            }
            throw $e;
        }
    }


    private function resolveSelectedHospital(HttpRequest $request, $user): ?Hospital
    {
        $fromQuery = $request->query('hospital_id');
        if ($fromQuery && Str::isUuid($fromQuery)) {
            $h = Hospital::query()
                ->where('id', $fromQuery)
                ->where('status', true)
                ->first();
            if ($h) {
                return $h;
            }
        }

        if ($user && $user->hospital_selected && Str::isUuid($user->hospital_selected)) {
            $h = Hospital::query()
                ->where('id', $user->hospital_selected)
                ->where('status', true)
                ->first();
            if ($h) {
                return $h;
            }
        }

        return null;
    }

    /**
     * Variables por defecto para encabezado (fecha y ventana activa).
     */
    private function defaultHeaderWindowVars(?Hospital $hospital): array
    {
        Carbon::setLocale('es');

        $now = Carbon::now();
        $fechaLarga = $now->translatedFormat('l, d \\de F \\de Y'); // lunes, 13 de octubre de 2025
        $activeWindow = $this->computeActiveWindowLabel($hospital);

        return [
            'todayHuman'   => $fechaLarga,
            'activeWindow' => $activeWindow, // puede ser null
        ];
    }

    /**
     * Obtiene etiqueta de “ventana activa” de forma segura si existe App\Support\MealWindow.
     */
    private function computeActiveWindowLabel(?Hospital $hospital): ?string
    {
        if (!$hospital) {
            return null;
        }

        $class = '\\App\\Support\\MealWindow';
        if (class_exists($class)) {
            try {
                if (method_exists($class, 'currentLabelForHospital')) {
                    return $class::currentLabelForHospital($hospital->id);
                }
                if (method_exists($class, 'labelForHospital')) {
                    return $class::labelForHospital($hospital->id);
                }
                if (method_exists($class, 'currentForHospital')) {
                    $res = $class::currentForHospital($hospital->id);
                    if (is_array($res) && !empty($res['meal'])) {
                        $from = $res['from'] ?? null;
                        $to   = $res['to'] ?? null;
                        return $from && $to ? "{$res['meal']} ({$from}–{$to})" : (string) $res['meal'];
                    }
                }
                $instance = new $class();
                if (method_exists($instance, 'currentLabelForHospital')) {
                    return $instance->currentLabelForHospital($hospital->id);
                }
                if (method_exists($instance, 'labelForHospital')) {
                    return $instance->labelForHospital($hospital->id);
                }
                if (method_exists($instance, 'current')) {
                    $res = $instance->current($hospital->id);
                    if (is_array($res) && !empty($res['meal'])) {
                        $from = $res['from'] ?? null;
                        $to   = $res['to'] ?? null;
                        return $from && $to ? "{$res['meal']} ({$from}–{$to})" : (string) $res['meal'];
                    }
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
    // --- Lee valores de ENUM desde INFORMATION_SCHEMA (maneja comas en el texto) ---
    
    // Helpers al final del controlador
    private function getEnumValues(string $table, string $column): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)
            || !\Illuminate\Support\Facades\Schema::hasColumn($table, $column)) {
            return [];
        }

        $db = \Illuminate\Support\Facades\DB::getDatabaseName();
        $row = \Illuminate\Support\Facades\DB::table('information_schema.columns')
            ->select('COLUMN_TYPE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->first();

        $columnType = $row->COLUMN_TYPE ?? null;
        if (!$columnType || stripos($columnType, 'enum(') !== 0) return [];

        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $columnType, $m);
        $vals = array_map(static fn($s) => stripslashes($s), $m[1] ?? []);
        
        return array_values(array_unique(array_filter($vals, fn($v) => $v !== '')));
    }

    private function loadDietTypesFromDb(): array
    {
        $types = $this->getEnumValues('collects', 'diet_type');
        if (!empty($types)) return $types;

        if (\Illuminate\Support\Facades\Schema::hasTable('collects')
        && \Illuminate\Support\Facades\Schema::hasColumn('collects', 'diet_type')) {
            $dist = \Illuminate\Support\Facades\DB::table('collects')
                ->distinct()->pluck('diet_type')->filter()->map(fn($v)=>(string)$v)->all();
            
            return array_values(array_unique($dist));
        }
        return [];
    }

    public function collectsSummary(\Illuminate\Http\Request $request)
    {
        $date       = $request->input('date');         // yyyy-mm-dd (opcional, default hoy)
        $hospitalId = $request->input('hospital_id');  // opcional
        $meal       = $request->input('meal');         // opcional

        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'invalid_date'], 422);
        }
        if (!$date) $date = now()->toDateString();

        $scope = function ($q) use ($date, $hospitalId, $meal) {
            $q->whereDate('date', $date);
            if ($hospitalId) $q->where('hospital_id', $hospitalId);
            if ($meal)       $q->where('meal', $meal);
        };

        // Solo la dieta principal puede ser desechable
        $disposableMain = "CASE WHEN (COALESCE(is_disposable, disposable, 0) = 1 OR packaging = 'desechable') THEN 1 ELSE 0 END";

        // Principal: siempre bandeja=1; desechable según flag (SOLO principal)
        $main = \DB::table('collects')
            ->selectRaw("diet_type as diet_type, 1 as bandeja, {$disposableMain} as desechable")
            ->whereNotNull('diet_type');
        $scope($main);

        // Acompañante: siempre bandeja=1; desechable=0 (NUNCA desechable)
        $companion = \DB::table('collects')
            ->selectRaw("companion_diet_type as diet_type, 1 as bandeja, 0 as desechable")
            ->where('has_companion', 1)
            ->whereNotNull('companion_diet_type');
        $scope($companion);

        // Unión y agregación por tipo de dieta
        $rows = \DB::query()
            ->fromSub($main->unionAll($companion), 'x')
            ->selectRaw('diet_type, SUM(bandeja) as bandeja, SUM(desechable) as desechable')
            ->groupBy('diet_type')
            ->get()
            ->map(fn($r) => [
                'diet_type'  => $r->diet_type,
                'bandeja'    => (int) $r->bandeja,
                'desechable' => (int) $r->desechable,
            ])
            ->values();

        return response()->json([
            'date'        => $date,
            'hospital_id' => $hospitalId,
            'meal'        => $meal,
            'summary'     => $rows,
        ]);
    }
    
    private function computeWindowFromDb(Hospital $hospital): ?array
    {
        $now   = \Carbon\Carbon::now();
        $today = $now->toDateString();
        $wins  = $this->buildWindowsForDate($hospital, $today);
        if (empty($wins)) return null;

        foreach ($wins as $w) {
            if ($now->between($w['from_dt'], $w['to_dt'])) {
                $next = $this->findNextWindowStart($hospital, $now);
                return [
                    'meal'     => $w['meal'],
                    'date'     => $today,
                    'from'     => $w['from_dt']->format('H:i'),
                    'to'       => $w['to_dt']->format('H:i'),
                    'label'    => "{$w['meal']} ({$w['from_dt']->format('H:i')}–{$w['to_dt']->format('H:i')})",
                    'latched'  => false,
                    'reset_at' => $next?->toIso8601String(),
                    'key'      => "{$today}|{$w['meal']}",
                ];
            }
        }

        // Latch a la última que ya terminó hoy
        $past = array_values(array_filter($wins, fn($w) => $w['to_dt']->lt($now)));
        if (!empty($past)) {
            $last = $past[count($past)-1];
            $next = $this->findNextWindowStart($hospital, $now);
            return [
                'meal'     => $last['meal'],
                'date'     => $today,
                'from'     => $last['from_dt']->format('H:i'),
                'to'       => $last['to_dt']->format('H:i'),
                'label'    => "{$last['meal']} ({$last['from_dt']->format('H:i')}–{$last['to_dt']->format('H:i')})",
                'latched'  => true,
                'reset_at' => $next?->toIso8601String(),
                'key'      => "{$today}|{$last['meal']}",
            ];
        }

        // Antes de la primera de hoy → engancha la última de ayer
        $yesterday = $now->copy()->subDay()->toDateString();
        $winsY     = $this->buildWindowsForDate($hospital, $yesterday);
        if (!empty($winsY)) {
            $last = $winsY[count($winsY)-1];
            $next = $this->findNextWindowStart($hospital, $now);
            return [
                'meal'     => $last['meal'],
                'date'     => $yesterday,
                'from'     => $last['from_dt']->format('H:i'),
                'to'       => $last['to_dt']->format('H:i'),
                'label'    => "{$last['meal']} ({$last['from_dt']->format('H:i')}–{$last['to_dt']->format('H:i')})",
                'latched'  => true,
                'reset_at' => $next?->toIso8601String(),
                'key'      => "{$yesterday}|{$last['meal']}",
            ];
        }

        return null;
    }

    private function buildWindowsForDate(Hospital $hospital, string $date): array
    {
        $defs = [
            ['meal' => 'Desayuno', 's' => 'breakfast_collection_start', 'e' => 'breakfast_collection_end'],
            ['meal' => 'Almuerzo', 's' => 'lunch_collection_start',     'e' => 'lunch_collection_end'],
            ['meal' => 'Cena',     's' => 'dinner_collection_start',    'e' => 'dinner_collection_end'],
        ];

        $wins = [];
        foreach ($defs as $d) {
            $rawStart = $hospital->{$d['s']} ?? null;
            $rawEnd   = $hospital->{$d['e']} ?? null;

            $start = $this->normalizeHospitalTime($rawStart);
            $end   = $this->normalizeHospitalTime($rawEnd);
            if (!$start || !$end) continue;

            // Construye DT consistentes: 'YYYY-MM-DD HH:MM:SS'
            $from = \Carbon\Carbon::parse("$date $start");
            $to   = \Carbon\Carbon::parse("$date $end");

            // Si el final es <= inicio, interpretamos cruce de medianoche
            if ($to->lte($from)) {
                $to->addDay();
            }

            $wins[] = ['meal' => $d['meal'], 'from_dt' => $from, 'to_dt' => $to];
        }

        usort($wins, fn($a, $b) => $a['from_dt'] <=> $b['from_dt']);
        return $wins;
    }

    private function findNextWindowStart(Hospital $hospital, \Carbon\Carbon $now): ?\Carbon\Carbon
    {
        $todayWins = $this->buildWindowsForDate($hospital, $now->toDateString());
        foreach ($todayWins as $w) if ($w['from_dt']->gt($now)) return $w['from_dt'];
        $tomWins = $this->buildWindowsForDate($hospital, $now->copy()->addDay()->toDateString());
        return $tomWins[0]['from_dt'] ?? null;
    }

    private function normalizeHospitalTime($val): ?string
    {
        if (!$val) return null;

        // Si llega ya como "HH:MM" o "HH:MM:SS", úsalo tal cual
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', (string)$val)) {
            // Asegura HH:MM:SS
            $parts = explode(':', (string)$val);
            if (count($parts) === 2) return $val . ':00';
            return (string)$val;
        }

        // Si llega con fecha y hora "YYYY-MM-DD HH:MM(:SS)?", toma solo la hora
        try {
            return \Carbon\Carbon::parse($val)->format('H:i:s');
        } catch (\Throwable $e) {
            return null; // Valor inválido
        }
    }


}
