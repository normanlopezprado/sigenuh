<?php
namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Collect;
use App\Models\Hospital;
use App\Models\Service;
use App\Support\MealWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CollectController extends Controller
{
    private const DIETS = [
        'Libre','Blanda','Hiposódica',
        'Diabético 1,200','Diabético 1,500',
        'Renal','Licuada','Especial',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $hospitalId = $user->hospital_selected;

        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning','Selecciona un hospital.');
        }
        $hospital = Hospital::findOrFail($hospitalId);

        $date = $request->query('date', now()->toDateString());
        $requestedMeal = $request->query('meal');
        $validMeals = ['Desayuno','Almuerzo','Cena'];

        if ($requestedMeal && in_array($requestedMeal, $validMeals, true)) {
            $meal = $requestedMeal;
        } else {
            $meal = MealWindow::currentMealPeriod($hospital);
            if (!$meal || !in_array($meal, $validMeals, true)) {
                $meal = 'Desayuno';
            }
        }
        $serviceId = $request->query('service');

        $services = Service::orderBy('name')->get();


        $bedsQuery = Bed::query()
            ->whereHas('hospitalFloorService.hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->with(['hospitalFloorService.service:id,name'])
            ->orderBy('code');

        if ($serviceId) {
            $bedsQuery->whereHas('hospitalFloorService', fn($q) => $q->where('service_id', $serviceId));
        }

        $beds    = $bedsQuery->get();
        $bedIds  = $beds->pluck('id')->all();


        $collects = Collect::whereIn('bed_id', $bedIds)
            ->whereDate('date', $date)
            ->where('meal', $meal)
            ->get()
            ->keyBy('bed_id');
        $prefillSource = null;

        if ($collects->isEmpty() && !empty($bedIds)) {
            $prevMeal = in_array($meal, $validMeals, true) ? $meal : null;
            $prevDate = Carbon::parse($date)->subDay()->toDateString();

            if ($prevMeal) {
                $prevCollectsQuery = Collect::whereIn('bed_id', $bedIds)
                    ->where('meal', $prevMeal);

                $prevCollects = (clone $prevCollectsQuery)
                    ->whereDate('date', $prevDate)
                    ->get()
                    ->keyBy('bed_id');

                if ($prevCollects->isEmpty()) {
                    $fallbackDate = (clone $prevCollectsQuery)
                        ->whereDate('date', '<', $date)
                        ->orderBy('date', 'desc')
                        ->value('date');

                    if ($fallbackDate) {
                        $prevDate = Carbon::parse($fallbackDate)->toDateString();
                        $prevCollects = (clone $prevCollectsQuery)
                            ->whereDate('date', $prevDate)
                            ->get()
                            ->keyBy('bed_id');
                    }
                }

                if ($prevCollects->isNotEmpty()) {
                    $collects      = $prevCollects;
                    $prefillSource = [
                        'date' => $prevDate,
                        'meal' => $prevMeal,
                    ];
                }
            }
        }
        $isOpen = in_array($meal, ['Desayuno','Almuerzo','Cena'])
            ? MealWindow::nowWithinHospitalWindow($hospital, $meal)
            : false;

        return view('collects.index', [
            'date'           => $date,
            'meal'           => $meal,
            'serviceId'      => $serviceId,
            'services'       => $services,
            'beds'           => $beds,
            'isOpen'         => $isOpen,
            'collectsByBed'  => $collects,
            'diets'          => self::DIETS,
            'prefillSource'  => $prefillSource
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $user = $request->user();
        $hospitalId = $user->hospital_selected;
        if (!$hospitalId) {
            return back()->with('warning','Selecciona un hospital.');
        }
        $hospital = Hospital::findOrFail($hospitalId);
        $data = $request->validate([
            'date' => ['required','date'],
            'meal' => ['required','in:Desayuno,Almuerzo,Cena'],
            'rows' => ['array'],
            'rows.*.diet_type' => ['nullable','in:Libre,Blanda,Hiposódica,Diabético 1,200,Diabético 1,500,Renal,Licuada,Especial'],
            'rows.*.has_disposable' => ['nullable','in:0,1'],
            'rows.*.notes' => ['nullable','string'],
        ]);

        $date = $data['date'];
        $meal = $data['meal'];
        $rows = $data['rows'] ?? [];
        if (!MealWindow::nowWithinHospitalWindow($hospital, $data['meal'])) {
            return back()->with('warning', 'Aún no está abierta la ventana de recolección para ' . $data['meal'] . '.');
        }
        DB::transaction(function() use ($rows, $date, $meal, $hospitalId, $user) {
            foreach ($rows as $bedId => $row) {
                $belongs = Bed::where('id', $bedId)
                    ->whereHas('hospitalFloorService.hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
                    ->exists();
                if (!$belongs) { continue; }

                Collect::updateOrCreate(
                    ['bed_id' => $bedId, 'date' => $date, 'meal' => $meal],
                    [
                        'diet_type'         => $row['diet_type'] ?? null,
                        'trays_count'       => isset($row['trays']) ? (int)$row['trays'] : 0,
                        'has_disposable' => isset($row['has_disposable']) ? (int)$row['has_disposable'] : 0,
                        'user_id'           => $user->id,
                        'notes'             => $row['notes'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success','Datos guardados.');
    }

    public function toggleBedStatus(Request $request, Bed $bed)
    {
        $user = $request->user();
        $hospitalId = $user->hospital_selected;

        $belongs = Bed::where('id', $bed->id)
            ->whereHas('hospitalFloorService.hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->exists();

        if (!$belongs) abort(403);

        $new = match ($bed->status) {
            'Disponible' => 'Ocupada',
            'Ocupada'    => 'Disponible',
            default      => 'Disponible',
        };

        $bed->update(['status' => $new]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'status' => $new]);
        }
        return back();
    }
    public function saveCompanion(Request $request, Bed $bed)
    {
        $user = $request->user();
        $hospitalId = $user->hospital_selected;
        if (!$hospitalId) abort(403);

        $belongs = Bed::where('id', $bed->id)
            ->whereHas('hospitalFloorService.hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->exists();
        if (!$belongs) abort(403);

        $data = $request->validate([
            'date'  => ['required','date'],
            'meal'  => ['required', Rule::in(['Desayuno','Almuerzo','Cena'])],
            'has_minor' => ['required','boolean'],
            'has_companion' => ['required','boolean'],
            'companion_diet_type' => [
                'nullable',
                Rule::in(['Libre','Blanda','Hiposódica','Diabético 1,200','Diabético 1,500','Renal','Licuada','Especial']),
            ],
            'companion_notes' => ['nullable','string'],
        ]);

        if (!$data['has_companion']) {
            $data['companion_diet_type'] = null;
            $data['companion_notes'] = null;
        }

        $collect = Collect::updateOrCreate(
            ['bed_id' => $bed->id, 'date' => $data['date'], 'meal' => $data['meal']],
            [
                'user_id'            => $user->id, // quién modifica
                'has_minor'          => (bool)$data['has_minor'],
                'has_companion'      => (bool)$data['has_companion'],
                'companion_diet_type'=> $data['companion_diet_type'] ?? null,
                'companion_notes'    => $data['companion_notes'] ?? null,
            ]
        );

        return response()->json([
            'ok' => true,
            'collect_id' => $collect->id,
            'has_minor' => $collect->has_minor,
            'has_companion' => $collect->has_companion,
            'companion_diet_type' => $collect->companion_diet_type,
        ]);
    }
}
