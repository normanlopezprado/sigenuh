<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\MenuIngredient;
use App\Models\CalendarMenuIngredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $calendars = Calendar::with('user')
            ->orderBy('date','desc')
            ->paginate(15);

        return view('calendars.index', compact('calendars'));
    }

    public function create()
    {
        return view('calendars.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'  => ['required','date'],
            'notes' => ['nullable','string'],
        ]);

        $data['user_id'] = $request->user()->id;

        $calendar = Calendar::create($data);

        return redirect()
            ->route('calendars.edit', $calendar)
            ->with('success', 'Calendario creado. Ahora puedes agregar los ingredientes opcionales.');
    }

    public function edit(Calendar $calendar)
    {
        $calendar->load([
            'optionalMenuIngredients' => function($q) { $q->orderBy('created_at','desc'); },
            'optionalMenuIngredients.menu',
        ]);
        $optionalItems = MenuIngredient::where('is_optional', true)
            ->with(['menu','ingredient'])
            ->orderBy('created_at','desc')
            ->get();

        return view('calendars.edit', compact('calendar','optionalItems'));
    }

    public function update(Request $request, Calendar $calendar)
    {
        $data = $request->validate([
            'date'  => ['required','date'],
            'notes' => ['nullable','string'],
        ]);

        $calendar->update($data);


        if ($request->has('selected_menu_ingredient_id')) {
            $ids = $request->input('selected_menu_ingredient_id', []);


            $validIds = MenuIngredient::whereIn('id', $ids)
                ->where('is_optional', true)
                ->pluck('id')->toArray();


            $syncPayload = [];
            foreach ($validIds as $id) {
                $syncPayload[$id] = [];
            }

            $calendar->optionalMenuIngredients()->sync($syncPayload);
        }

        return redirect()
            ->route('calendars.edit', $calendar)
            ->with('success','Calendario actualizado.');
    }

    public function destroy(Calendar $calendar)
    {
        $calendar->delete();
        return redirect()->route('calendars.index')->with('success','Calendario eliminado.');
    }
    public function monthData(Request $request)
    {
        // year y month vienen del JS. Si no, usar hoy.
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        // Rango del mes visible
        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // Traemos los calendarios del mes con sus opcionales
        $calendars = Calendar::with([
            'optionalMenuIngredients' => function($q) {
                $q->with(['menu','ingredient']); // por si quieres mostrar info general
            }
        ])->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        // OPCIONAL: si tienes una tabla (p.ej. calendar_menu) que asocia menú al día y tipo (desayuno/almuerzo/cena),
        // puedes cargarla aquí y poblar 'meals'. Por ahora, usaremos 'opcionales' para marcar que "hay algo".

        // Estructura de salida esperada por el frontend adaptado:
        // [
        //   {
        //     "date": "2025-09-22",
        //     "items": [
        //        {"label": "Desayuno", "color": "blue",   "summary": "Menú X ..."},
        //        {"label": "Almuerzo", "color": "orange", "summary": "Menú Y ..."},
        //        {"label": "Cena",     "color": "green",  "summary": "Menú Z ..."}
        //     ]
        //   }, ...
        // ]
        //
        // Si todavía NO tienes por-comida (D/A/C), pintaremos un único "General"
        // en amarillo cuando existan opcionales ese día.

        $out = [];

        foreach ($calendars as $cal) {
            $items = [];

            // Si aún no hay asignación explícita por comida, marcamos "General" (amarillo) si hay opcionales:
            if ($cal->optionalMenuIngredients->count() > 0) {
                $summary = 'Opcionales: ' .
                    $cal->optionalMenuIngredients->take(3)->map(function($p){
                        // Muestra ingrediente o menú segun prefieras
                        return optional($p->ingredient)->name ?? 'Item';
                    })->implode(', ') .
                    ($cal->optionalMenuIngredients->count() > 3 ? '…' : '');

                $items[] = [
                    'label'   => 'General',
                    'color'   => 'yellow',  // leyenda existente en tu CSS
                    'summary' => $summary,
                ];
            }

            // Si en el futuro tienes calendar_menu con meal = breakfast|lunch|dinner,
            // aquí mapea cada meal a un color:
            // breakfast -> blue, lunch -> orange, dinner -> green

            $out[] = [
                'date'  => $cal->date->format('Y-m-d'),
                'items' => $items,
            ];
        }

        return response()->json([
            'year'  => $year,
            'month' => $month,
            'events'=> $out,
        ]);
    }
}
