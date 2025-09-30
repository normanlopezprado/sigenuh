<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Beneficiary;
use App\Models\StaffMealRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema; // IMPORTANTE

class StaffMealController extends Controller
{
    /**
     * Pantalla de entrega
     * GET /staff-meals/entrega
     */
    public function create(Request $request)
    {
        $mealType = $request->get('meal_type', 'desayuno');
        $menus = $this->menusFor($mealType);

        return view('staff_meals.delivery', [
            'mealType' => $mealType,
            'menus'    => $menus,
        ]);
    }

    /**
     * Guardar la entrega
     * POST /staff-meals/entrega
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'meal_type'      => 'required|in:desayuno,almuerzo,cena',
            'menu_id'        => 'required|exists:menus,id',
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'notes'          => 'nullable|string|max:500',
        ]);

        $data['delivered_by'] = Auth::id();
        $data['delivered_at'] = Carbon::now();

        StaffMealRecord::create($data);

        return response()->json([
            'ok'      => true,
            'message' => 'Entrega registrada correctamente.',
        ]);
    }

    /**
     * AJAX: Obtener menús según tipo de comida
     */
    public function menusByMealType(Request $request)
    {
        $request->validate([
            'meal_type' => 'required|in:desayuno,almuerzo,cena',
        ]);

        $menus = $this->menusFor($request->meal_type)->map(fn ($m) => [
            'id'   => $m->id,
            'name' => $m->name,
        ]);

        return response()->json($menus);
    }

    /**
     * AJAX: Buscar beneficiarios
     */
    public function searchBeneficiaries(Request $request)
    {
        $q = trim($request->get('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $beneficiarios = \App\Models\Beneficiary::query()
            ->where('status', true)
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json(
            $beneficiarios->map(fn ($b) => [
                'id'   => $b->id,
                'name' => $b->name,
            ])
        );
    }

    /**
     * AJAX: Ver entregas de hoy
     */
    public function todayDeliveries(Request $request)
    {
        $request->validate([
            'meal_type' => 'required|in:desayuno,almuerzo,cena',
        ]);

        $start = Carbon::today();
        $end   = Carbon::tomorrow();

        $records = StaffMealRecord::with(['beneficiary', 'menu', 'deliveredBy'])
            ->where('meal_type', $request->meal_type)
            ->whereBetween('delivered_at', [$start, $end])
            ->latest('delivered_at')
            ->get();

        $rows = $records->map(function ($r) {
            return [
                'id'           => $r->id,
                'beneficiary'  => optional($r->beneficiary)->first_name . ' ' . optional($r->beneficiary)->last_name,
                'position'     => optional($r->beneficiary)->position,
                'menu'         => optional($r->menu)->name,
                'delivered_by' => optional($r->deliveredBy)->name,
                'delivered_at' => optional($r->delivered_at)?->format('Y-m-d H:i:s'),
                'notes'        => (string) ($r->notes ?? ''),
            ];
        });

        return response()->json($rows);
    }

    /**
     * Helper: Obtener menús para un tipo de comida sin depender de columnas
     */
    private function menusFor(string $mealType)
    {
        $q = Menu::query();

        // Filtrar por status si existe
        if (Schema::hasColumn('menus', 'status')) {
            $q->where('status', 1);
        }

        // Filtrar por diet_type si existe
        if (Schema::hasColumn('menus', 'diet_type')) {
            $q->where('diet_type', 'Libre');
        }

        // Si existe una columna meal_type, la usamos
        if (Schema::hasColumn('menus', 'meal_type')) {
            $q->where('meal_type', $mealType);
        } else {
            // Fallback: buscar por texto en el nombre
            $needle = match ($mealType) {
                'desayuno' => 'desayuno',
                'almuerzo' => 'almuerzo',
                'cena'     => 'cena',
            };
            $q->where('name', 'like', '%' . $needle . '%');
        }

        return $q->orderBy('name')->get(['id', 'name']);
    }
}
