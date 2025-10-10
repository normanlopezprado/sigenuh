<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Models\Menu;
use App\Models\StaffMealRecord;

class StaffMealReportController extends Controller
{

    public function deliveriesReport(Request $request)
    {
        $defaults = [
            'date_from'   => now()->subDays(7)->toDateString(),
            'date_to'     => now()->toDateString(),
            'meal_type'   => null,
            'diet_type'   => null,
            'benef_q'     => null,
            'menu_q'      => null,
            'delivered_q' => null,
            'per_page'    => 25,
        ];

        $filters = [
            'date_from'   => $request->input('date_from', $defaults['date_from']),
            'date_to'     => $request->input('date_to', $defaults['date_to']),
            'meal_type'   => $request->input('meal_type'),
            'diet_type'   => $request->input('diet_type'),
            'benef_q'     => trim((string) $request->input('benef_q')),
            'menu_q'      => trim((string) $request->input('menu_q')),
            'delivered_q' => trim((string) $request->input('delivered_q')),
            'per_page'    => (int) $request->input('per_page', $defaults['per_page']),
        ];

        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
            'meal_type' => ['nullable', 'string'],
            'diet_type' => ['nullable', 'string'],
            'per_page'  => ['nullable', 'integer', 'min:10', 'max:200'],
        ]);

        // Base query (listado)
        $base = StaffMealRecord::query()
            ->join('staff_beneficiaries as sb', 'sb.id', '=', 'staff_meal_records.staff_beneficiary_id')
            ->leftJoin('menus', 'menus.id', '=', 'staff_meal_records.menu_id')
            ->leftJoin('users as u', 'u.id', '=', 'staff_meal_records.delivered_by')
            ->select([
                'staff_meal_records.id',
                'staff_meal_records.delivery_date',
                'staff_meal_records.meal_type',
                'staff_meal_records.delivered_at',
                'sb.full_name as beneficiary',
                'menus.name as menu_name',
                'menus.diet_type as diet_type',
                'u.name as delivered_by_name',
            ]);

        if ($filters['date_from']) {
            $base->whereDate('staff_meal_records.delivery_date', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $base->whereDate('staff_meal_records.delivery_date', '<=', $filters['date_to']);
        }
        if ($filters['meal_type']) {
            $base->where('staff_meal_records.meal_type', $filters['meal_type']);
        }
        if ($filters['diet_type']) {
            $base->where('menus.diet_type', $filters['diet_type']);
        }
        if ($filters['benef_q']) {
            $base->where('sb.full_name', 'like', '%'.$filters['benef_q'].'%');
        }
        if ($filters['menu_q']) {
            $base->where('menus.name', 'like', '%'.$filters['menu_q'].'%');
        }
        if ($filters['delivered_q']) {
            $base->where('u.name', 'like', '%'.$filters['delivered_q'].'%');
        }

        // Export CSV (con mismos filtros)
        if ($request->filled('export') && $request->input('export') === 'csv') {
            $rows = (clone $base)
                ->orderBy('staff_meal_records.delivery_date', 'desc')
                ->orderBy('staff_meal_records.delivered_at', 'desc')
                ->get();

            $filename = 'entregas_'.now()->format('Ymd_His').'.csv';
            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['Fecha', 'Hora', 'Tiempo de comida', 'Beneficiario', 'Menú', 'Tipo de dieta', 'Entregado por']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        Carbon::parse($r->delivery_date)->toDateString(),
                        optional($r->delivered_at)->format('H:i:s'),
                        ucfirst($r->meal_type),
                        $r->beneficiary,
                        $r->menu_name,
                        $r->diet_type,
                        $r->delivered_by_name,
                    ]);
                }
                fclose($out);
            };

            return Response::stream($callback, 200, $headers);
        }

        $rows = (clone $base)
            ->orderBy('staff_meal_records.delivery_date', 'desc')
            ->orderBy('staff_meal_records.delivered_at', 'desc')
            ->paginate($filters['per_page'])
            ->appends($filters);

        $dietTypes = Menu::query()
            ->select('diet_type')
            ->whereNotNull('diet_type')
            ->where('diet_type', '!=', '')
            ->distinct()
            ->orderBy('diet_type')
            ->pluck('diet_type')
            ->all();

        $mealTypes = ['desayuno', 'almuerzo', 'cena'];

        $deliveredNames = StaffMealRecord::query()
            ->leftJoin('users as u', 'u.id', '=', 'staff_meal_records.delivered_by')
            ->when($filters['date_from'], fn ($q) => $q->whereDate('staff_meal_records.delivery_date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('staff_meal_records.delivery_date', '<=', $filters['date_to']))
            ->whereNotNull('u.name')
            ->distinct()
            ->orderBy('u.name')
            ->pluck('u.name')
            ->all();

        $summary = (clone $base)
            ->reorder() 
            ->select('staff_meal_records.meal_type') 
            ->selectRaw('COUNT(*) AS c')
            ->groupBy('staff_meal_records.meal_type')
            ->pluck('c', 'staff_meal_records.meal_type');

        return view('staff_meals.report', [
            'rows'           => $rows,
            'filters'        => $filters,
            'dietTypes'      => $dietTypes,
            'mealTypes'      => $mealTypes,
            'deliveredNames' => $deliveredNames,
            'summary'        => $summary,
        ]);
    }
}
