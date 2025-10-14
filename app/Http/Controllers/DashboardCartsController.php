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

        
        $cartsTable    = (new Cart)->getTable();                   
        $pivotTable    = 'cart_service';
        $hfsTable      = (new HospitalFloorService)->getTable();   
        $hfTable       = (new HospitalFloor)->getTable();          
        $nivelsTable   = (new Nivel)->getTable();                  
        $servicesTable = (new Service)->getTable();                
        $bedsTable     = 'beds';                                   

        try {
            
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
                $payload = []; 
                return response()->json([
                    'carts'         => $payload,
                    'active_window' => $win['label'] ?? null,
                    'window'        => $win,
                    'window_key'    => $win['key'] ?? null, 
                    'server_time'   => now()->toIso8601String(),
                ]);
            }

            $cartIds = $carts->pluck('id');

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
                DB::raw("CAST(REGEXP_REPLACE(COALESCE(n.name, ''), '[^0-9]', '') AS UNSIGNED) as floor_order"),
                
                $serviceCategoryCol
                    ? DB::raw("COALESCE(s.`{$serviceCategoryCol}`, '') as categoria")
                    : DB::raw("'' as categoria"),
            ])
            ->orderBy('floor_order', 'desc')   
            ->orderBy('servicio', 'asc')       
            ->orderBy('categoria', 'asc')      
            ->get();

        $pathsByCart = $pathsRows->groupBy('cart_id')->map(function ($rows) {
            return collect($rows)->map(function ($r) {
                
                $nivel     = property_exists($r, 'nivel')     ? trim((string)$r->nivel)     : '';
                $servicio  = property_exists($r, 'servicio')  ? trim((string)$r->servicio)  : '';
                $categoria = property_exists($r, 'categoria') ? trim((string)$r->categoria) : '';

                $servicioCat = trim($servicio . ' ' . $categoria);

                return collect([ $nivel !== '' ? $nivel : null, $servicioCat !== '' ? $servicioCat : null ])
                    ->filter()
                    ->implode(' — ');
            })->filter()->values()->all();
        });

            $date = $win['date'] ?? now()->toDateString();
            $meal = $win['meal'] ?? null;


    
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
                'window'        => $this->computeWindowFromDb($hospital), 
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


    private function defaultHeaderWindowVars(?Hospital $hospital): array
    {
        Carbon::setLocale('es');

        $now = Carbon::now();
        $fechaLarga = $now->translatedFormat('l, d \\de F \\de Y'); 
        $activeWindow = $this->computeActiveWindowLabel($hospital);

        return [
            'todayHuman'   => $fechaLarga,
            'activeWindow' => $activeWindow, 
        ];
    }

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
        $date       = $request->input('date');         
        $hospitalId = $request->input('hospital_id');  
        $meal       = $request->input('meal');         

        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'invalid_date'], 422);
        }
        if (!$date) $date = now()->toDateString();

        $scope = function ($q) use ($date, $hospitalId, $meal) {
            $q->whereDate('date', $date);
            if ($hospitalId) $q->where('hospital_id', $hospitalId);
            if ($meal)       $q->where('meal', $meal);
        };

        $disposableMain = "CASE WHEN (COALESCE(is_disposable, disposable, 0) = 1 OR packaging = 'desechable') THEN 1 ELSE 0 END";

        $main = \DB::table('collects')
            ->selectRaw("diet_type as diet_type, 1 as bandeja, {$disposableMain} as desechable")
            ->whereNotNull('diet_type');
        $scope($main);

        $companion = \DB::table('collects')
            ->selectRaw("companion_diet_type as diet_type, 1 as bandeja, 0 as desechable")
            ->where('has_companion', 1)
            ->whereNotNull('companion_diet_type');
        $scope($companion);

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

            $from = \Carbon\Carbon::parse("$date $start");
            $to   = \Carbon\Carbon::parse("$date $end");

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

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', (string)$val)) {
            $parts = explode(':', (string)$val);
            if (count($parts) === 2) return $val . ':00';
            return (string)$val;
        }

        try {
            return \Carbon\Carbon::parse($val)->format('H:i:s');
        } catch (\Throwable $e) {
            return null; 
        }
    }

}
