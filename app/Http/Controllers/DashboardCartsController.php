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

    /**
     * Endpoint JSON: carritos asignados a servicios de un hospital.
     * GET /dashboard/carts/live?hospital_id=UUID
     */
    public function live(HttpRequest $request)
{
    $validated = $request->validate([
        'hospital_id' => ['required', 'uuid'],
    ]);
    $hospitalId = $validated['hospital_id'];

    $hospital = Hospital::where('id', $hospitalId)
        ->where('status', true)
        ->firstOrFail();

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
            return response()->json([
                'carts'         => [],
                'active_window' => $this->computeActiveWindowLabel($hospital),
                'server_time'   => now()->toIso8601String(),
            ]);
        }

        $cartIds = $carts->pluck('id');

        // 2) Servicios por carrito — "Nivel — Servicio Categoria"
        $serviceCategoryCol = Schema::hasColumn($servicesTable, 'categoria')
            ? 'categoria'
            : (Schema::hasColumn($servicesTable, 'category') ? 'category' : null);

        $selects = [
            'cs.cart_id',
            'n.name as nivel',
            's.name as servicio',
            DB::raw("CAST(REGEXP_REPLACE(COALESCE(n.name, ''), '[^0-9]', '') AS UNSIGNED) as floor_order"),
        ];
        $selects[] = $serviceCategoryCol
            ? DB::raw("s.`{$serviceCategoryCol}` as categoria")
            : DB::raw("NULL as categoria");

        $pathsRows = DB::table("$pivotTable as cs")
            ->join("$hfsTable as hfs", 'hfs.id', '=', 'cs.hospital_floor_service_id')
            ->join("$hfTable as hf", 'hf.id', '=', 'hfs.hospital_floor_id')
            ->leftJoin("$nivelsTable as n", 'n.id', '=', 'hf.nivel_id')
            ->leftJoin("$servicesTable as s", 's.id', '=', 'hfs.service_id')
            ->where('hf.hospital_id', $hospitalId)
            ->whereIn('cs.cart_id', $cartIds)
            ->select($selects)
            ->orderBy('floor_order', 'desc')  // piso DESC
            ->orderBy('s.name', 'asc')        // servicio ASC
            ->when($serviceCategoryCol, fn($q) => $q->orderBy("s.$serviceCategoryCol", 'asc'))
            ->get();

        $pathsByCart = $pathsRows->groupBy('cart_id')->map(function ($rows) {
            return collect($rows)->map(function ($r) {
                $nivel     = $r->nivel ? trim($r->nivel) : null;
                $servicio  = $r->servicio ? trim($r->servicio) : '';
                $categoria = $r->categoria ? trim($r->categoria) : '';
                $servicioCat = trim($servicio . ' ' . $categoria); // "Traumatología Mujeres"
                return collect([$nivel, $servicioCat])->filter()->implode(' — ');
            })->filter()->values()->all();
        });

        // 3) Contadores (regla exclusiva: principal Bandeja/O Desechable; acompañante solo Bandeja)
        $date = $request->input('date');
        $meal = $request->input('meal');
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'invalid_date'], 422);
        }
        if (!$date) $date = now()->toDateString();

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
    

}
