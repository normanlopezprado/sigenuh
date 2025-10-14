<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\CalendarMenuIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $calendars = Calendar::with('user')
            ->orderBy('date','desc');

        return view('calendars.index', compact('calendars'));
    }

    public function create(Request $request)
    {
        $categories = ['desayuno','almuerzo','cena'];
        $dietTypes  = [
            'Libre',
            'Blanda',
            'Hiposódica',
            'Diabético 1,200',
            'Diabético 1,500',
            'Renal',
            'Licuada',
            'Blanda 8m',
            'Papilla',
            'Especial',
        ];
        $cat  = $request->query('category');
        $diet = $request->query('diet_type');

        $menus = Menu::query()
            ->when($cat,  fn($qq) => $qq->where('category', $cat))
            ->when($diet, fn($qq) => $qq->where('diet_type', $diet))
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $date = $request->query('date');
        return view('calendars.create', compact('menus','categories','dietTypes','cat','diet','date'));

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'  => ['required','date'],
            'notes' => ['nullable','string'],
            'menu_id' => ['nullable','string'],
        ]);

        $data['user_id'] = Auth::id();
        $calendar = Calendar::create($data);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Menú asignado correctamente.');
    }

    public function edit(Calendar $calendar, Request $request)
    {
        $calendar->load(['menu']);

        $optionalItems = collect();
        if ($calendar->menu_id) {
            $optionalItems = MenuIngredient::where('menu_id', $calendar->menu_id)
                ->where('is_optional', true)
                ->with(['menu','ingredient'])
                ->orderBy('id')
                ->get();
        }

        $selectedOptionalIds = $calendar->optionalMenuIngredients()
            ->pluck('menu_ingredient.id')
            ->toArray();

        return view('calendars.edit', compact(
            'calendar', 'optionalItems', 'selectedOptionalIds'
        ));
    }

    public function update(Request $request, Calendar $calendar)
    {
        $data = $request->validate([
            'notes' => ['nullable','string','max:500'],
            'selected_menu_ingredient_id'   => ['array'],
            'selected_menu_ingredient_id.*' => ['uuid'],
        ]);


        $calendar->update([
            'notes' => $data['notes'] ?? null,
        ]);


        $allowedOptionalIds = MenuIngredient::where('menu_id', $calendar->menu_id)
            ->where('is_optional', true)
            ->pluck('id')->toArray();

        $requestedIds = $data['selected_menu_ingredient_id'] ?? [];
        $finalIds = array_values(array_intersect($requestedIds, $allowedOptionalIds));


        $payload = [];
        foreach ($finalIds as $id) {
            $payload[$id] = [];
        }

        $calendar->optionalMenuIngredients()->sync($payload);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Calendario actualizado. Opcionales guardados correctamente.');
    }

    public function destroy(Calendar $calendar)
    {
        $calendar->delete();
        return redirect()->route('calendars.index')->with('success','Calendario eliminado.');
    }
    public function monthData(Request $request)
    {
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $calendars = Calendar::query()
            ->with('menu')
            ->whereBetween('calendars.date', [$start, $end])
            ->join('menus', 'calendars.menu_id', '=', 'menus.id')
            ->orderByRaw("
                    CASE menus.category
                        WHEN 'Desayuno' THEN 1
                        WHEN 'Almuerzo' THEN 2
                        WHEN 'Cena' THEN 3
                        ELSE 4
                    END
                ")
            ->orderBy('calendars.date')
            ->select('calendars.*')
            ->get();
        $events = [];
        foreach ($calendars as $cal) {
            if ($cal->menu) {
                $color = match ($cal->menu->category) {
                    'Desayuno' => 'blue',
                    'Almuerzo' => 'orange',
                    'Cena'     => 'green',
                };
                $events[] = [
                    'date'  => $cal->date->format('Y-m-d'),
                    'items' => [[
                        'label'   => 'Menú',
                        'color'   =>  $color,
                        'summary' => $cal->menu->name,
                        'editUrl' => route('calendars.edit', $cal->id),
                    ]],
                ];
            } else {

            }
        }

        return response()->json([
            'year'   => $year,
            'month'  => $month,
            'events' => $events,
        ]);
    }
}
