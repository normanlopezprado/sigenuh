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

    // Nombres de tabla desde los modelos (solo strings, no aplica scopes)
    $cartsTable    = (new Cart)->getTable();                   // 'carts'
    $pivotTable    = 'cart_service';
    $hfsTable      = (new HospitalFloorService)->getTable();   // 'hospital_floor_services'
    $hfTable       = (new HospitalFloor)->getTable();          // 'hospital_floors'
    $nivelsTable   = (new Nivel)->getTable();                  // 'nivels'
    $servicesTable = (new Service)->getTable();                // 'services'

    try {
        // 1) Carts del hospital (sin SoftDeletes; ORDER escapado)
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

        // 2) Rutas "Nivel — Servicio" por cart
        $cartIds = $carts->pluck('id');

        $pathsRows = DB::table("$pivotTable as cs")
            ->join("$hfsTable as hfs", 'hfs.id', '=', 'cs.hospital_floor_service_id')
            ->join("$hfTable as hf", 'hf.id', '=', 'hfs.hospital_floor_id')
            ->leftJoin("$nivelsTable as n", 'n.id', '=', 'hf.nivel_id')
            ->leftJoin("$servicesTable as s", 's.id', '=', 'hfs.service_id')
            ->where('hf.hospital_id', $hospitalId)
            ->whereIn('cs.cart_id', $cartIds)
            ->select('cs.cart_id', 'n.name as nivel', 's.name as servicio')
            ->get();

        $pathsByCart = $pathsRows->groupBy('cart_id')->map(function ($rows) {
            return collect($rows)->map(function ($r) {
                return collect([$r->nivel ?: null, $r->servicio ?: null])
                    ->filter()->implode(' — ');
            })->filter()->unique()->values()->all();
        });

        // 3) Shape para el front
        $payload = $carts->map(function ($c) use ($pathsByCart) {
            return [
                'id'              => $c->id,
                'name'            => $c->name,
                'code_name'       => $c->code_name,
                'color'           => $c->color,
                'order'           => $c->order,
                'status'          => (bool) $c->status,
                'notes'           => $c->notes,
                'services_count'  => (int) ($c->services_count_for_hospital ?? 0),
                'service_paths'   => $pathsByCart->get($c->id, []),
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

    /* =======================
     *  Helpers privados
     * ======================= */

    /**
     * Resuelve el hospital seleccionado:
     * - ?hospital_id en la URL (UUID, activo)
     * - $user->hospital_selected (UUID, activo)
     * - null si no hay selección válida
     */
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
}
