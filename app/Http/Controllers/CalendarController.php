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
            ->route('calendars.index', $calendar)
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
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $calendars = Calendar::with('menu')
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();
        $events = [];
        foreach ($calendars as $cal) {
            if ($cal->menu) {
                $color = match ($cal->menu->category) {
                    'desayuno' => 'blue',
                    'almuerzo' => 'orange',
                    'cena'     => 'green',
                };
                $events[] = [
                    'date'  => $cal->date->format('Y-m-d'),
                    'items' => [[
                        'label'   => 'Menú',
                        'color'   =>  $color,
                        'summary' => $cal->menu->name,
                    ]],
                ];
            } else {
                // Si hay calendar sin menú, puedes decidir mostrar “Sin menú” o no incluirlo.
                // $events[] = [
                //     'date'  => $cal->date->format('Y-m-d'),
                //     'items' => [[ 'label'=>'Menú', 'color'=>'yellow', 'summary'=>'(Sin menú)' ]],
                // ];
            }
        }

        return response()->json([
            'year'   => $year,
            'month'  => $month,
            'events' => $events,
        ]);
    }
}
