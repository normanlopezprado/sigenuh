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
    
        // === SEXO (Hombres / Mujeres / Niños) ===
        // Histórico por defecto; si viene date_range -> filtra por c.date
        $sexQ = DB::table('collects as c')
            ->join('beds as b', 'b.id', '=', 'c.bed_id')
            ->join('hospital_floor_services as hfs', 'hfs.id', '=', 'b.hospital_floor_service_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            ->selectRaw("UPPER(TRIM(s.category)) as cat, COUNT(*) as total")
            ->whereIn(DB::raw("UPPER(TRIM(s.category))"), ['HOMBRES','MUJERES','MENORES','NIÑOS'])
            ->groupBy('cat');

        if ($applyRange) {
            $sexQ->whereBetween('c.date', [$start->toDateString(), $end->toDateString()]);
        }

        $rawSex = $sexQ->pluck('total', 'cat')->toArray();

        // Suma "MENORES" + "NIÑOS" (y cubre sin acento por si acaso)
        $kids = (int) (($rawSex['MENORES'] ?? 0) + ($rawSex['NIÑOS'] ?? 0) + ($rawSex['NINOS'] ?? 0));

        $sexPayload = [
            'labels' => ['Hombres', 'Mujeres', 'Niños'],
            'data'   => [
                (int)($rawSex['HOMBRES'] ?? 0),
                (int)($rawSex['MUJERES'] ?? 0),
                $kids,
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
                // Normaliza: decodifica, recorta y convierte "+" en espacio por si acaso
                $raw = urldecode(trim($range));
                $raw = str_replace('+', ' ', $raw); // blindaje si llega literal con +

                // Acepta "YYYY-MM-DD - YYYY-MM-DD" o "YYYY-MM-DD-YYYY-MM-DD" (sin espacios)
                if (preg_match('/^\s*(\d{4}-\d{2}-\d{2})\s*-\s*(\d{4}-\d{2}-\d{2})\s*$/', $raw, $m)) {
                    $start = Carbon::parse($m[1])->startOfDay();
                    $end   = Carbon::parse($m[2])->endOfDay();
                } else {
                    // Si solo viene una fecha, usa ese mismo día
                    $start = Carbon::parse($raw)->startOfDay();
                    $end   = Carbon::parse($raw)->endOfDay();
                }
            } catch (\Throwable $e) {
                // fallback seguro: hoy
                $start = Carbon::today()->startOfDay();
                $end   = Carbon::today()->endOfDay();
            }
        }

        return [$start, $end];
    }
}
