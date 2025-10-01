<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Menu;
use App\Models\Calendar;
use App\Models\StaffBeneficiary;
use App\Models\StaffMealRecord;

class StaffMealController extends Controller
{
    /**
     * GET /staff_meals/delivery
     * Renderiza la vista principal con categorías y fecha de hoy.
     */
    public function delivery(Request $request)
    {
        $mealType = $request->get('meal_type', 'desayuno'); // valor inicial opcional
        $today    = Carbon::today();

        // Distinct de categorías (menus.category)
        $categories = Menu::query()
            ->where('status', 1)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->all();

        return view('staff_meals.delivery', compact('mealType', 'today', 'categories'));
    }

    /**
     * POST /staff_meals/deliver
     * Evita duplicados por (staff_beneficiary_id, meal_type, delivery_date).
     * Requiere confirmar contraseña del usuario autenticado.
     */
    public function deliver(Request $request)
    {
        $data = $request->validate([
            'staff_beneficiary_id' => ['required', 'uuid', 'exists:staff_beneficiaries,id'],
            'meal_type'            => ['required', 'string'], // desayuno | almuerzo | cena
            'delivery_date'        => ['required', 'date'],
            'menu_id'              => ['required', 'uuid', 'exists:menus,id'],
            'confirm_password'     => ['required', 'current_password'], // ← valida contra el usuario logueado
        ]);

        // Derivar hospital si aplica
        $hospitalId = $request->user()->hospital_selected ?? null;
        if (!$hospitalId) {
            $hospitalId = StaffBeneficiary::where('id', $data['staff_beneficiary_id'])->value('hospital_id');
        }

        // Duplicado por beneficiario + tiempo + fecha
        $exists = StaffMealRecord::query()
            ->where('staff_beneficiary_id', $data['staff_beneficiary_id'])
            ->where('meal_type', $data['meal_type'])
            ->whereDate('delivery_date', Carbon::parse($data['delivery_date'])->toDateString())
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este beneficiario ya tiene registrada la entrega para este tiempo de comida en esta fecha.'
            ], 409);
        }

        $rec = new StaffMealRecord();
        $rec->staff_beneficiary_id = $data['staff_beneficiary_id'];
        $rec->meal_type            = $data['meal_type'];
        $rec->delivery_date        = Carbon::parse($data['delivery_date'])->toDateString();
        $rec->menu_id              = $data['menu_id'];
        $rec->delivered_at         = now(); // hora exacta
        $rec->hospital_id          = $hospitalId ?? null;            // si existe en tu tabla
        $rec->delivered_by         = optional($request->user())->id; // si existe en tu tabla
        $rec->save();

        return response()->json([
            'message' => 'Entrega registrada con éxito.',
            'id'      => $rec->id,
        ]);
    }

    /**
     * GET /staff_meals/options/diet-types?category=desayuno
     * -> ['libre','blanda', ...]
     */
    public function dietTypes(Request $request)
    {
        $request->validate(['category' => ['required', 'string']]);

        $dietTypes = Menu::query()
            ->where('status', 1)
            ->where('category', $request->input('category'))
            ->select('diet_type')
            ->distinct()
            ->orderBy('diet_type')
            ->pluck('diet_type');

        return response()->json($dietTypes);
    }

    /**
     * GET /staff_meals/options/menus-today?category=desayuno&diet_type=libre&date=YYYY-MM-DD
     * -> [{id: 'calendar_id', menu_id: 'uuid', text: 'Nombre del menú'}, ...] (solo UI)
     */
    public function menusToday(Request $request)
    {
        $request->validate([
            'category'  => ['required', 'string'],
            'diet_type' => ['required', 'string'],
            'date'      => ['required', 'date'],
        ]);

        $day = Carbon::parse($request->input('date'))->startOfDay();

        $rows = Calendar::query()
            ->join('menus', 'calendars.menu_id', '=', 'menus.id')
            ->whereDate('calendars.date', $day)
            ->where('menus.status', 1)
            ->where('menus.category', $request->input('category'))
            ->where('menus.diet_type', $request->input('diet_type'))
            ->orderBy('menus.name')
            ->get([
                'calendars.id as calendar_id',
                'menus.id as menu_id',
                'menus.name as name',
                'menus.category as category',
                'menus.diet_type as diet_type',
            ]);

        $out = $rows->map(fn ($r) => [
            'id'      => $r->calendar_id, // id de calendars (opcional para UI)
            'menu_id' => $r->menu_id,     // necesario para guardar
            'text'    => $r->name,
        ])->values();

        return response()->json($out);
    }

    /**
     * GET /staff_meals/search-beneficiaries?q=...
     * -> [{id, full_name}, ...] (solo activos)
     */
    public function searchBeneficiaries(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $beneficiaries = StaffBeneficiary::query()
            ->where('status', 1)
            ->where('full_name', 'like', '%' . $q . '%')
            ->orderBy('full_name')
            ->limit(10)
            ->get(['id', 'full_name']);

        return response()->json($beneficiaries);
    }

    /**
     * GET /staff_meals/list-deliveries?date=YYYY-MM-DD&meal_type=desayuno
     * Devuelve entregas del día por tiempo de comida (para la tabla).
     */
    public function listDeliveries(Request $request)
    {
        $request->validate([
            'date'      => ['required', 'date'],
            'meal_type' => ['required', 'string'],
        ]);

        $day = Carbon::parse($request->input('date'))->toDateString();

        $rows = StaffMealRecord::query()
            ->join('staff_beneficiaries as sb', 'sb.id', '=', 'staff_meal_records.staff_beneficiary_id')
            ->leftJoin('menus', 'menus.id', '=', 'staff_meal_records.menu_id')
            ->leftJoin('users as u', 'u.id', '=', 'staff_meal_records.delivered_by') // ← traer quién entregó
            ->whereDate('staff_meal_records.delivery_date', $day)
            ->where('staff_meal_records.meal_type', $request->input('meal_type'))
            ->orderBy('staff_meal_records.delivered_at', 'desc')
            ->get([
                'staff_meal_records.id',
                'sb.full_name as beneficiary',
                'menus.name as menu_name',
                'staff_meal_records.delivered_at',
                'u.name as delivered_by_name',
            ])
            ->map(function ($r) {
                return [
                    'id'               => $r->id,
                    'beneficiary'      => $r->beneficiary,
                    'menu_name'        => $r->menu_name ?? '—',
                    'delivered_at'     => Carbon::parse($r->delivered_at)->format('H:i:s'),
                    'delivered_by_name'=> $r->delivered_by_name ?? '—',
                ];
            });

        return response()->json($rows);
    }
}
