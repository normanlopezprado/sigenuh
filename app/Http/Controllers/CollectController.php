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
        $meal = $request->query('meal', 'Desayuno');
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
            // calcular el turno anterior
            $prevDate = Carbon::parse($date)->toDateString();
            $prevMeal = null;

            switch ($meal) {
                case 'Desayuno':
                    $prevDate = Carbon::parse($date)->subDay()->toDateString();
                    $prevMeal = 'Cena';
                    break;
                case 'Almuerzo':
                    $prevMeal = 'Desayuno';
                    break;
                case 'Cena':
                    $prevMeal = 'Almuerzo';
                    break;
            }
            if ($prevMeal) {
                $prevCollects = Collect::whereIn('bed_id', $bedIds)
                    ->whereDate('date', $prevDate)
                    ->where('meal', $prevMeal)
                    ->get()
                    ->keyBy('bed_id');

                if ($prevCollects->isNotEmpty()) {
                    $collects      = $prevCollects;
                    $prefillSource = [
                        'date' => $prevDate,
                        'meal' => $prevMeal,
                    ];
                }
            }
        }
        $meal = $request->query('meal');
        if (!$meal || !in_array($meal, ['Desayuno','Almuerzo','Cena'])) {
            $meal = MealWindow::currentMealPeriod($hospital);
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
            'rows.*.trays' => ['nullable','integer','min:0'],
            'rows.*.disposables' => ['nullable','integer','min:0'],
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
                // Verifica que la cama pertenece al hospital del usuario
                $belongs = Bed::where('id', $bedId)
                    ->whereHas('hospitalFloorService.hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
                    ->exists();
                if (!$belongs) { continue; }

                Collect::updateOrCreate(
                    ['bed_id' => $bedId, 'date' => $date, 'meal' => $meal],
                    [
                        'diet_type'         => $row['diet_type'] ?? null,
                        'trays_count'       => isset($row['trays']) ? (int)$row['trays'] : 0,
                        'disposables_count' => isset($row['disposables']) ? (int)$row['disposables'] : 0,
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
}
