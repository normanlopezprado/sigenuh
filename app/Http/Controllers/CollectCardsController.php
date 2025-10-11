<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\HospitalFloorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CollectCardsController extends Controller
{
    /**
     * GET /collects/cards
     */
    public function index(Request $request)
    {
        // === Hospital activo ===
        $hospitalId = $request->query('hospital_id')
            ?? session('hospital_id')
            ?? Hospital::query()->value('id');

        if (!$hospitalId) {
            return back()->with('warning', 'No hay hospital activo configurado.');
        }

        // === Fecha y comida (el meal se refleja en el título y se reenvía como hidden) ===
        $date = $request->query('date', Carbon::now()->toDateString());
        $meal = $request->query('meal', 'Desayuno'); // Desayuno | Almuerzo | Cena

        // === Servicios del hospital: construir display_levels/abbr/name/cat y ordenar por piso (5to, 4to, ...) ===
        $rawServices = Service::query()
            ->with(['hospitalFloors' => function ($q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId)->with('nivel');
            }])
            ->whereHas('hospitalFloors', fn ($q) => $q->where('hospital_id', $hospitalId))
            ->get();

        $services = $rawServices->map(function (Service $svc) {
            $levels = $svc->hospitalFloors
                ->map(fn ($hf) => optional($hf->nivel)->name)
                ->filter()
                ->unique()
                ->values();

            return (object) [
                'id'             => $svc->id,
                'display_levels' => $levels->isNotEmpty() ? $levels->join(', ') : null,
                'abbreviation'   => $svc->abbreviation,
                'name'           => $svc->name,
                'category'       => $svc->category,
            ];
        })
        ->sortByDesc(function ($svc) {
            // Extrae el número del nivel (e.g., "5to", "4to", "2do")
            if (preg_match('/(\d+)/', $svc->display_levels ?? '', $m)) {
                return (int) $m[1];
            }
            return 0; // sin número => al final
        })
        ->values();

        // === Servicio seleccionado ===
        $serviceId = $request->query('service');

        // === Camas del servicio (del hospital activo) ===
        $beds = collect();
        if ($serviceId) {
            $hfsIds = HospitalFloorService::query()
                ->where('service_id', $serviceId)
                ->whereHas('hospitalFloor', fn ($q) => $q->where('hospital_id', $hospitalId))
                ->pluck('id');

            if ($hfsIds->isNotEmpty()) {
                $beds = Bed::query()
                    ->with(['hospitalFloorService.service']) // la vista arma títulos con esto
                    ->whereIn('hospital_floor_service_id', $hfsIds)
                    ->orderBy('code')
                    ->get();
            }
        }

        // === Prefill de datos anteriores para la fecha + meal seleccionados ===
        $collectsByBed = [];
        if ($beds->isNotEmpty()) {
            $bedIds = $beds->pluck('id')->all();

            $rows = DB::table('collects')
                ->select(
                    'bed_id','diet_type','has_disponsable','has_minor','minor_age','has_companion',
                    'companion_diet_type','notes','companion_notes','trays_count','disposables_count'
                )
                ->whereIn('bed_id', $bedIds)
                ->whereDate('date', $date)
                ->where('meal', $meal)
                ->get();

            foreach ($rows as $r) {
                $collectsByBed[$r->bed_id] = [
                    'diet_type'           => $r->diet_type,
                    'has_disponsable'     => (int) $r->has_disponsable === 1,
                    'has_minor'           => (int) $r->has_minor === 1,
                    'minor_age'           => $r->minor_age,
                    'has_companion'       => (int) $r->has_companion === 1,
                    'companion_diet_type' => $r->companion_diet_type,
                    'notes'               => $r->notes,
                    'companion_notes'     => $r->companion_notes,
                    'trays_count'         => $r->trays_count,
                    'disposables_count'   => $r->disposables_count,
                ];
            }
        }

        // === Variables que usa la vista ===
        $isOpen        = true; // si tienes lógica real de ventana activa, sustitúyela
        $diets         = ['Libre','Blanda','Hiposódica','1,200','Diabético','Renal','Licuada','Especial'];
        $prefillSource = null;

        return view('collects.cards', compact(
            'date', 'meal', 'serviceId', 'services', 'beds',
            'isOpen', 'collectsByBed', 'diets', 'prefillSource'
        ));
    }

    /**
     * PATCH /collects/bed/{bed}/toggle
     * Trabaja con ENUM('Disponible','Ocupada') en beds.status
     */
    public function toggleAvailability(Request $request, Bed $bed)
    {
        // Front envía to_busy=1 si quiere marcar Ocupada; 0 => Disponible
        $toBusy = $request->input('to_busy', null);
        if ($toBusy !== null) {
            $bed->status = $toBusy ? 'Ocupada' : 'Disponible';
        } else {
            $bed->status = ($bed->status === 'Ocupada') ? 'Disponible' : 'Ocupada';
        }

        $bed->save();

        return response()->json([
            'ok'     => true,
            'status' => $bed->status, // texto exacto del enum
        ]);
    }

    /**
     * POST /collects/bulk
     * Guarda en `collects` por (bed_id, date, meal) vía upsert manual.
     */
    public function bulkStore(Request $request)
    {
        $date = $request->input('date', Carbon::now()->toDateString());
        $meal = $request->input('meal'); // viene del título "Recolección de: XXX"

        // Validación cabecera
        Validator::make(
            ['date' => $date, 'meal' => $meal],
            [
                'date' => ['required','date'],
                'meal' => ['required', Rule::in(['Desayuno','Almuerzo','Cena'])],
            ],
            [],
            ['meal' => 'comida']
        )->validate();

        $rows = $request->input('rows', []);
        if (empty($rows) || !is_array($rows)) {
            return back()->with('warning', 'No hay datos para guardar.')->withInput();
        }

        $userId   = Auth::id();
        $dietEnum = ['Libre','Blanda','Hiposódica','1,200','Diabético','Renal','Licuada','Especial'];

        DB::beginTransaction();
        try {
            foreach ($rows as $bedId => $data) {

                // UUID válido en key
                if (!Str::isUuid((string) $bedId)) {
                    continue;
                }

                // marker para garantizar fila presente
                if (!array_key_exists('__present', (array)$data)) {
                    continue;
                }

                // Normalizar checkboxes (presente => 1, ausente => 0)
                $hasMinor        = !empty($data['has_minor']) ? 1 : 0;
                $hasDisponsable  = !empty($data['has_disponsable']) ? 1 : 0; // nombre exacto de la columna en BD
                $hasCompanion    = !empty($data['has_companion']) ? 1 : 0;

                // Counts NOT NULL: default 0
                $traysCount       = isset($data['trays_count']) ? (int) $data['trays_count'] : 0;
                $disposablesCount = isset($data['disposables_count']) ? (int) $data['disposables_count'] : 0;

                // Si la fila no tiene nada que guardar, saltamos
                $hasAny =
                    $hasMinor === 1 ||
                    $hasDisponsable === 1 ||
                    $hasCompanion === 1 ||
                    !empty($data['diet_type']) ||
                    !empty($data['companion_diet_type']) ||
                    isset($data['notes']) ||
                    isset($data['companion_notes']);

                if (!$hasAny) {
                    continue;
                }

                // Validación por fila
                Validator::make(
                    [
                        'bed_id'              => $bedId,
                        'diet_type'           => $data['diet_type']           ?? null,
                        'has_disponsable'     => $hasDisponsable,
                        'trays_count'         => $traysCount,
                        'disposables_count'   => $disposablesCount,
                        'has_minor'           => $hasMinor,
                        'minor_age'           => $data['minor_age']           ?? null,
                        'has_companion'       => $hasCompanion,
                        'companion_diet_type' => $data['companion_diet_type'] ?? null,
                        'notes'               => $data['notes']               ?? null,
                        'companion_notes'     => $data['companion_notes']     ?? null,
                    ],
                    [
                        'bed_id'              => ['required','uuid','exists:beds,id'],
                        'diet_type'           => ['nullable', Rule::in($dietEnum)],
                        'has_disponsable'     => ['required','boolean'],
                        'trays_count'         => ['required','integer','min:0'],
                        'disposables_count'   => ['required','integer','min:0'],
                        'has_minor'           => ['required','boolean'],
                        'minor_age'           => [$hasMinor ? 'required' : 'nullable','integer','min:0','max:120'],
                        'has_companion'       => ['required','boolean'],
                        'companion_diet_type' => ['nullable', Rule::in($dietEnum)],
                        'notes'               => ['nullable','string'],
                        'companion_notes'     => ['nullable','string'],
                    ],
                    [],
                    [
                        'diet_type'           => 'dieta',
                        'companion_diet_type' => 'dieta del acompañante',
                        'minor_age'           => 'edad del menor',
                    ]
                )->validate();

                // Upsert por (bed_id, date, meal)
                $existing = DB::table('collects')
                    ->where('bed_id', $bedId)
                    ->whereDate('date', $date)
                    ->where('meal', $meal)
                    ->first();

                $payload = [
                    'bed_id'               => (string) $bedId,
                    'date'                 => $date,
                    'meal'                 => $meal,
                    'diet_type'            => $data['diet_type']           ?? null,
                    'trays_count'          => $traysCount,         // 0 por defecto
                    'disposables_count'    => $disposablesCount,   // 0 por defecto
                    'user_id'              => $userId,
                    'notes'                => $data['notes']               ?? null,
                    'has_minor'            => $hasMinor,
                    'has_companion'        => $hasCompanion,
                    'companion_diet_type'  => $data['companion_diet_type'] ?? null,
                    'companion_notes'      => $data['companion_notes']     ?? null,
                    'has_disponsable'      => $hasDisponsable,     // respeta nombre de columna
                    'updated_at'           => now(),
                ];

                if ($existing) {
                    DB::table('collects')->where('id', $existing->id)->update($payload);
                } else {
                    $payload['id'] = (string) Str::uuid();
                    $payload['created_at'] = now();
                    DB::table('collects')->insert($payload);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Ocurrió un error al guardar: '.$e->getMessage())->withInput();
        }

        return back()->with('success', 'Recolección guardada correctamente.');
    }
}
