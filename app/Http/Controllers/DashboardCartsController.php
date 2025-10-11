<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Models\Hospital;
use App\Models\Cart;
use Carbon\Carbon;

class DashboardCartsController extends Controller
{
    public function index(Request $request)
    {
        $hospitals = Hospital::select('id', 'name')->orderBy('name')->get();

        $selectedHospitalId = $request->query('hospital_id') ?? $hospitals->first()->id ?? null;
        $hospital = $selectedHospitalId ? Hospital::find($selectedHospitalId) : null;
        $activeDate = now()->toDateString();

        // Determinar ventana activa (basado en horarios del hospital)
        [$activeMeal, $winStart, $winEnd] = $this->resolveActiveMealWindowFromHospital($hospital, $activeDate);

        // Obtener carritos del hospital
        $carts = $this->queryCarts($selectedHospitalId);

        // Obtener dietas desde collects dentro de la ventana activa
        $dietTypes = $this->dietTypesFromActiveWindow($selectedHospitalId, $winStart, $winEnd);

        // Agregados por carrito y dieta
        $cartDietStats = $this->buildCartDietStats($selectedHospitalId, $winStart, $winEnd);

        return view('dashboard.cars', [
            'hospitals'          => $hospitals,
            'selectedHospitalId' => $selectedHospitalId,
            'carts'              => $carts,
            'dietTypes'          => $dietTypes,
            'cartDietStats'      => $cartDietStats,
            'activeDate'         => $activeDate,
            'activeMeal'         => $activeMeal,
        ]);
    }

    public function partial(Request $request)
    {
        $hospitalId = $request->query('hospital_id');
        $hospital   = $hospitalId ? Hospital::find($hospitalId) : null;
        $activeDate = now()->toDateString();

        [$activeMeal, $winStart, $winEnd] = $this->resolveActiveMealWindowFromHospital($hospital, $activeDate);

        $carts = $this->queryCarts($hospitalId);
        $dietTypes = $this->dietTypesFromActiveWindow($hospitalId, $winStart, $winEnd);
        $cartDietStats = $this->buildCartDietStats($hospitalId, $winStart, $winEnd);

        $html = View::make('partials.dashboard.carts-cards', [
            'carts'         => $carts,
            'dietTypes'     => $dietTypes,
            'cartDietStats' => $cartDietStats,
            'activeDate'    => $activeDate,
        ])->render();

        return response()->json(['ok' => true, 'html' => $html]);
    }

    private function resolveActiveMealWindowFromHospital(?Hospital $hospital, string $date): array
    {
        $d = Carbon::parse($date);

        $def = [
            'Desayuno' => ['05:00:00','10:59:59'],
            'Almuerzo' => ['11:00:00','15:59:59'],
            'Cena'     => ['16:00:00','23:59:59'],
        ];

        $bkStart = $hospital->breakfast_start ?? $def['Desayuno'][0];
        $bkEnd   = $hospital->breakfast_end   ?? $def['Desayuno'][1];
        $lnStart = $hospital->lunch_start     ?? $def['Almuerzo'][0];
        $lnEnd   = $hospital->lunch_end       ?? $def['Almuerzo'][1];
        $dnStart = $hospital->dinner_start    ?? $def['Cena'][0];
        $dnEnd   = $hospital->dinner_end      ?? $def['Cena'][1];

        $windows = [
            'Desayuno' => [Carbon::parse("$date $bkStart"), Carbon::parse("$date $bkEnd")],
            'Almuerzo' => [Carbon::parse("$date $lnStart"), Carbon::parse("$date $lnEnd")],
            'Cena'     => [Carbon::parse("$date $dnStart"), Carbon::parse("$date $dnEnd")],
        ];

        $now = now();
        foreach ($windows as $meal => [$start, $end]) {
            if ($now->between($start, $end)) {
                return [$meal, $start->toDateTimeString(), $end->toDateTimeString()];
            }
        }

        return ['Cena', $windows['Cena'][0]->toDateTimeString(), $windows['Cena'][1]->toDateTimeString()];
    }

    private function queryCarts(?string $hospitalId)
    {
        return Cart::query()
            ->when($hospitalId, fn($q) => $q->where('hospital_id', $hospitalId))
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    private function dietTypesFromActiveWindow(?string $hospitalId, string $winStart, string $winEnd)
    {
        return DB::table('collects as c')
            ->join('beds as b', 'b.id', '=', 'c.bed_id')
            ->join('cart_service as cs', 'cs.hospital_floor_service_id', '=', 'b.hospital_floor_service_id')
            ->join('carts as ca', 'ca.id', '=', 'cs.cart_id')
            ->when($hospitalId, fn($q) => $q->where('ca.hospital_id', $hospitalId))
            ->whereBetween('c.created_at', [$winStart, $winEnd])
            ->whereNotNull('c.diet_type')
            ->distinct()
            ->pluck('c.diet_type')
            ->sort();
    }

    private function buildCartDietStats(?string $hospitalId, string $winStart, string $winEnd): array
    {
        $rows = DB::table('collects as c')
            ->join('beds as b', 'b.id', '=', 'c.bed_id')
            ->join('cart_service as cs', 'cs.hospital_floor_service_id', '=', 'b.hospital_floor_service_id')
            ->join('carts as ca', 'ca.id', '=', 'cs.cart_id')
            ->when($hospitalId, fn($q) => $q->where('ca.hospital_id', $hospitalId))
            ->whereBetween('c.created_at', [$winStart, $winEnd])
            ->select(
                'ca.id as cart_id',
                'c.diet_type',
                DB::raw('COUNT(*) as trays'),
                DB::raw('SUM(CASE WHEN c.has_disponsable = 1 THEN 1 ELSE 0 END) as disposables')
            )
            ->groupBy('ca.id', 'c.diet_type')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[$r->cart_id][$r->diet_type] = [
                'trays' => (int) $r->trays,
                'disposables' => (int) $r->disposables,
            ];
        }
        return $out;
    }
}
