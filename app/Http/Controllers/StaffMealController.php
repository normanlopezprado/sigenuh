<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\StaffBeneficiary;
use App\Models\StaffMealRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StaffMealController extends Controller
{
    
    public function delivery(Request $request)
    {
        $mealType = $request->get('meal_type'); 
        return view('staff_meals.delivery', compact('mealType'));
    }

    public function searchBeneficiaries(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $hospitalId = $request->user()->hospital_selected;

        $items = StaffBeneficiary::query()
            ->when($hospitalId, fn($q2) => $q2->where('hospital_id', $hospitalId))
            ->where('status', 1)
            ->where('full_name', 'like', '%' . $q . '%')
            ->orderBy('full_name')
            ->limit(15)
            ->get(['id', 'full_name']);

        return response()->json($items);
    }

    // GET /staff_meals/suggest-menus?meal_type=desayuno
    public function suggestMenus(Request $request)
    {
        $mealType = $request->get('meal_type', 'desayuno');

        // Si tu tabla menus NO tiene meal_type/diet_type, no intentes filtrar
        $hasMealType = Schema::hasColumn('menus', 'meal_type');
        $hasStatus   = Schema::hasColumn('menus', 'status');

        $q = Menu::query();
        if ($hasStatus) $q->where('status', 1);
        if ($hasMealType) $q->where('meal_type', $mealType);
        // Si no tiene columnas, devolveremos una lista corta (o vacía)
        $menus = $q->orderBy('name')->limit(20)->get(['id', 'name']);

        return response()->json($menus);
    }

    // POST /staff_meals
    public function store(Request $request)
    {
        $today = Carbon::now()->toDateString();

        $validated = $request->validate([
            'staff_beneficiary_id' => ['required','uuid', Rule::exists('staff_beneficiaries','id')],
            'meal_type'            => ['required', Rule::in(['desayuno','almuerzo','cena'])],
            // si se envía otra fecha (p.e. re-proceso), la aceptamos
            'delivery_date'        => ['nullable','date'],
            'menu_id'              => ['nullable','uuid', Rule::exists('menus','id')],
            'menu_text'            => ['nullable','string','max:255'],
            'notes'                => ['nullable','string','max:2000'],
        ]);

        $hospitalId   = $request->user()->hospital_selected;
        $deliveryDate = $validated['delivery_date'] ?? $today;

        // Evita duplicados por índice único de la DB
        $record = StaffMealRecord::firstOrCreate(
            [
                'staff_beneficiary_id' => $validated['staff_beneficiary_id'],
                'meal_type'            => $validated['meal_type'],
                'delivery_date'        => $deliveryDate,
            ],
            [
                'hospital_id' => $hospitalId,
                'delivered_by'=> $request->user()->id,
                'menu_id'     => $validated['menu_id']   ?? null,
                'menu_text'   => $validated['menu_text'] ?? null,
                'notes'       => $validated['notes']     ?? null,
            ]
        );

        // Si ya existía, avisamos que no se duplicó
        if (!$record->wasRecentlyCreated) {
            return response()->json([
                'ok' => false,
                'message' => 'Ya existe una entrega registrada para este beneficiario, tipo y fecha.'
            ], 409);
        }

        return response()->json(['ok' => true, 'message' => 'Entrega registrada.']);
    }

    // GET /staff_meals
    public function index(Request $request)
    {
        $hospitalId = $request->user()->hospital_selected;

        $q = StaffMealRecord::query()
            ->with(['beneficiary','deliveredBy'])
            ->when($hospitalId, fn($qq) => $qq->where('hospital_id', $hospitalId))
            ->when($request->filled('meal_type'), fn($qq) => $qq->where('meal_type', $request->meal_type))
            ->when($request->filled('date'), fn($qq) => $qq->whereDate('delivery_date', $request->date))
            ->when($request->filled('s'), function ($qq) use ($request) {
                $term = '%'.$request->s.'%';
                $qq->whereHas('beneficiary', fn($b) => $b->where('full_name', 'like', $term));
            })
            ->orderByDesc('delivery_date')
            ->orderBy('meal_type')
            ->limit(500);

        $records = $q->get();

        return view('staff_meals.index', compact('records'));
    }

    // DELETE /staff_meals/{record}
    public function destroy(StaffMealRecord $record)
    {
        $record->delete();
        return redirect()->route('staff_meals.index')->with('success', 'Entrega anulada.');
    }
}
