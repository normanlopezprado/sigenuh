<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StatsReportController extends Controller
{
    // -------------------------------------------------------------------------
    // [ACCION] Reporte principal
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        // rango de fechas        
        $rawRange = $request->query('date_range', $request->input('date_range', ''));
        $applyRange = filled($rawRange);

        $rawRange = str_replace('+', ' ', $rawRange);

        [$start, $end] = $this->parseRange($rawRange);


        // ---
        // KPIs
        // ---
        // KPI: Pacientes atendidos = número de filas en collects dentro del rango
        $pacientesQ = DB::table('collects');
        if ($applyRange) {
            $pacientesQ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $pacientes = (int) $pacientesQ
        ->count();

        // KPI: Acompañantes atendidos = collects con has_companion=1
        $acompanantesQ = DB::table('collects')->where('has_companion', 1);
        if ($applyRange) {
            $acompanantesQ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $acompanantes = (int) $acompanantesQ
        ->count();

        // KPI: Colaboradores atendidos = cada fila en staff_meal_records
        $colaboradoresQ = DB::table('staff_meal_records');
        if ($applyRange) {
            $colaboradoresQ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()]);
        }
        $colaboradores = (int) $colaboradoresQ
        ->count();

        // KPI: Bandejas entregadas = collects donde NO es desechable (has_disponsable ≠ 1)
        $bandejasQ = DB::table('collects')->whereRaw('COALESCE(has_disponsable, 0) <> 1');
        if ($applyRange) {
            $bandejasQ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $bandejas = (int) $bandejasQ
        ->count();

        // KPI: Desechables entregados = collects con has_disponsable = 1
        $desechablesQ = DB::table('collects')->where('has_disponsable', 1);
        if ($applyRange) {
            $desechablesQ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        }
        $desechables = (int) $desechablesQ
        ->count();

        // ------
        // [CHARTs]
        // ------
    
        // === SEXO (Hombres / Mujeres / Niños) con regla de has_minor =================
    
        $sexBase = DB::table('collects as c')
            ->join('beds as b', 'b.id', '=', 'c.bed_id')
            ->join('hospital_floor_services as hfs', 'hfs.id', '=', 'b.hospital_floor_service_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            ->where(function ($q) {
                $q->whereIn(DB::raw("UPPER(TRIM(s.category))"), ['HOMBRES','MUJERES','MENORES','NIÑOS','NINOS'])
                ->orWhereRaw('COALESCE(c.has_minor,0) = 1');
            });

        if ($applyRange) {
            $sexBase->whereBetween('c.date', [$start->toDateString(), $end->toDateString()]);
        }

        $sexQ = $sexBase->selectRaw("
            CASE
                WHEN COALESCE(c.has_minor,0) = 1
                    OR UPPER(TRIM(s.category)) IN ('MENORES','NIÑOS','NINOS')
                THEN 'Niños'
                WHEN UPPER(TRIM(s.category)) = 'HOMBRES'
                THEN 'Hombres'
                WHEN UPPER(TRIM(s.category)) = 'MUJERES'
                THEN 'Mujeres'
                ELSE 'Otros'
            END AS bucket,
            COUNT(*) AS total
        ")->groupBy('bucket');

        $rawSex = $sexQ->pluck('total', 'bucket')->toArray();

        $sexPayload = [
            'labels' => ['Hombres', 'Mujeres', 'Niños'],
            'data'   => [
                (int)($rawSex['Hombres'] ?? 0),
                (int)($rawSex['Mujeres'] ?? 0),
                (int)($rawSex['Niños']   ?? 0), 
            ],
        ];



        // === CHART: Dietas entregadas  ==================
        // Contamos cada fila por su tipo de dieta; si hay acompañante, sumamos su dieta.
        $dietPac = DB::table('collects as c')
            ->when($applyRange, fn($q) => $q->whereBetween('c.date', [$start->toDateString(), $end->toDateString()]))
            ->selectRaw("COALESCE(NULLIF(TRIM(c.diet_type), ''), '(Sin dieta)') as label, COUNT(*) as total")
            ->groupBy('label')
            ->pluck('total', 'label')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $dietComp = DB::table('collects as c')
            ->where('c.has_companion', 1)
            ->when($applyRange, fn($q) => $q->whereBetween('c.date', [$start->toDateString(), $end->toDateString()]))
            ->selectRaw("COALESCE(NULLIF(TRIM(c.companion_diet_type), ''), '(Sin dieta)') as label, COUNT(*) as total")
            ->groupBy('label')
            ->pluck('total', 'label')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        
        $dietCountsMap = [];
        foreach ([$dietPac, $dietComp] as $arr) {
            foreach ($arr as $label => $cnt) {
                $dietCountsMap[$label] = ($dietCountsMap[$label] ?? 0) + (int) $cnt;
            }
        }
        
        uksort($dietCountsMap, fn($a, $b) => $dietCountsMap[$b] <=> $dietCountsMap[$a]);

        $dietPayload = [
            'labels' => array_keys($dietCountsMap),
            'data'   => array_values($dietCountsMap),
        ];



        // === CHART: Comidas por servicio (Stacked Bar) =============================
        // Series = { Desayuno, Almuerzo, Cena }, Categorías = Servicios

        $meals = ['Desayuno','Almuerzo','Cena'];

        $svcMealRows = DB::table('collects as c')
            ->join('beds as b', 'b.id', '=', 'c.bed_id')
            ->join('hospital_floor_services as hfs', 'hfs.id', '=', 'b.hospital_floor_service_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            ->select('s.name as service', 'c.meal', DB::raw('COUNT(*) as total'))
            ->when($applyRange, fn($q) => $q->whereBetween('c.date', [$start->toDateString(), $end->toDateString()]))
            ->groupBy('service', 'c.meal')
            ->get();

        $svcMealMap = [];      
        $totalPerSvc = [];     

        foreach ($svcMealRows as $r) {
            $svc  = (string) $r->service;
            $meal = (string) $r->meal;
            $cnt  = (int) $r->total;

            if (!isset($svcMealMap[$svc])) {
                $svcMealMap[$svc] = array_fill_keys($meals, 0);
                $totalPerSvc[$svc] = 0;
            }

            if (in_array($meal, $meals, true)) {
                $svcMealMap[$svc][$meal] += $cnt;
                $totalPerSvc[$svc]       += $cnt;
            }
        }

        uksort($svcMealMap, function($a, $b) use ($totalPerSvc) {
            return ($totalPerSvc[$b] <=> $totalPerSvc[$a]) ?: strcasecmp($a, $b);
        });

        $categories = array_keys($svcMealMap);

        $series = [];
        foreach ($meals as $meal) {
            $series[] = [
                'name' => $meal,
                'data' => array_map(fn($svc) => (int) ($svcMealMap[$svc][$meal] ?? 0), $categories),
            ];
        }

        $mealsStackedPayload = [
            'categories' => $categories, 
            'series'     => $series,     
        ];




        // === CHART: Pirámide por servicio (pacientes + acompañantes) ===
        // Cuenta 1 por cada collect + 1 extra si has_companion=1 (misma fila).
        $pyrQ = DB::table('collects as c')
            ->join('beds as b', 'b.id', '=', 'c.bed_id')
            ->join('hospital_floor_services as hfs', 'hfs.id', '=', 'b.hospital_floor_service_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            ->select('s.name',
                DB::raw('COUNT(*) + SUM(CASE WHEN c.has_companion=1 THEN 1 ELSE 0 END) as total')
            );

        if ($applyRange) {
            $pyrQ->whereBetween('c.date', [$start->toDateString(), $end->toDateString()]);
        }

        $svcCounts = $pyrQ
            ->groupBy('s.name')
            ->pluck('total', 's.name')
            ->map(fn($v) => (int)$v)
            ->toArray();

        arsort($svcCounts);

        $pyramidPayload = [
            'categories' => array_keys($svcCounts),   // Servicios (labels)
            'data'       => array_values($svcCounts), // Totales
        ];






        // ---
        // Render
        // ---
        return view('stats.report', [
            'today' => Carbon::today(),
            'kpis'  => [
                'pacientes' => (int) $pacientes,
                'acompanantes'  => (int) $acompanantes,
                'colaboradores' => (int) $colaboradores,
                'bandejas'      => (int) $bandejas,
                'desechables'   => (int) $desechables,




            ],
            'dietCountsMap' => $dietCountsMap,
            'sexPayload'    => $sexPayload, 
            'dietPayload'  => $dietPayload,
            'mealsStackedPayload' => $mealsStackedPayload,
            'pyramidPayload'  => $pyramidPayload,  


        ]);
    }

    // -------------------------------------------------------------------------
    // [HELPER] Parse date range from string (robusto con +, espacios, o sin espacios)
    // -------------------------------------------------------------------------
    private function parseRange(?string $range): array
    {
        $start = Carbon::today()->startOfDay();
        $end   = Carbon::today()->endOfDay();

        if ($range) {
            try {
                
                $raw = urldecode(trim($range));
                $raw = str_replace('+', ' ', $raw); 

                
                if (preg_match('/^\s*(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})\s*$/', $raw, $m)) {
                    $start = Carbon::parse($m[1])->startOfDay();
                    $end   = Carbon::parse($m[2])->endOfDay();
                } else {
                
                    $start = Carbon::parse($raw)->startOfDay();
                    $end   = Carbon::parse($raw)->endOfDay();
                }
            } catch (\Throwable $e) {
                
                $start = Carbon::today()->startOfDay();
                $end   = Carbon::today()->endOfDay();
            }
        }

        return [$start, $end];
    }
}
