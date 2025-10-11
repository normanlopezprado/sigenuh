<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\HospitalFloorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CollectCardsController extends Controller
{
    public function index(Request $request)
    {
        // Hospital activo (URL ?hospital_id=, sesión, o primero)
        $hospitalId = $request->query('hospital_id')
            ?? session('hospital_id')
            ?? Hospital::query()->value('id');

        if (!$hospitalId) {
            return back()->with('warning', 'No hay hospital activo configurado.');
        }

        // Fecha y ventana (tu Blade los muestra)
        $date = $request->query('date', Carbon::now()->toDateString());
        $meal = $request->query('meal', 'Desayuno'); // puedes cambiar el default

        // === SERVICES ===
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

            $displayLevels = $levels->isNotEmpty() ? $levels->join(', ') : null;

            return (object) [
                'id'             => $svc->id,
                'display_levels' => $displayLevels,
                'abbreviation'   => $svc->abbreviation,
                'name'           => $svc->name,
                'category'       => $svc->category,
            ];
        })
        ->sortByDesc(function ($svc) {
            // Extraer número del nivel (ej. "5to" → 5)
            if (preg_match('/(\d+)/', $svc->display_levels ?? '', $m)) {
                return (int) $m[1];
            }
            return 0; // si no tiene número, va al final
        })
        ->values();

        // Servicio seleccionado
        $serviceId = $request->query('service');

        // === BEDS ===
        $beds = collect();
        if ($serviceId) {
            $hfsIds = HospitalFloorService::query()
                ->where('service_id', $serviceId)
                ->whereHas('hospitalFloor', fn ($q) => $q->where('hospital_id', $hospitalId))
                ->pluck('id');

            if ($hfsIds->isNotEmpty()) {
                $beds = Bed::query()
                    ->with(['hospitalFloorService.service']) // <- la vista arma $svcTitle desde aquí
                    ->whereIn('hospital_floor_service_id', $hfsIds)
                    ->orderBy('code')
                    ->get()
                    // Asegurar que cada cama tenga una clave 'status' entendible por la vista
                    ->map(function (Bed $b) {
                        // Si tu columna es booleana (true = disponible), la convertimos a texto
                        // Cambia esta lógica si en tu BD 'status' ya es string.
                        $statusBool = (bool) ($b->status ?? true);
                        $b->status = $statusBool ? 'Disponible' : 'Ocupada';
                        return $b;
                    });
            }
        }

        // === Extras que tu Blade usa ===
        $isOpen        = true;   // ajusta tu lógica real de ventana activa si aplica
        $collectsByBed = [];     // prefill (si tuvieras datos previos)
        $diets         = ['Libre','Blanda','Hiposódica','Diabética','Líquida','Licuada']; // llena según tu catálogo
        $prefillSource = null;

        return view('collects.cards', compact(
            'date', 'meal', 'serviceId', 'services', 'beds',
            'isOpen', 'collectsByBed', 'diets', 'prefillSource'
        ));
    }

    public function toggleAvailability(Request $request, Bed $bed)
    {
        // Tu JS puede enviar to_busy=1 cuando se desmarca el switch
        $toBusy = (int) $request->input('to_busy', null);

        // Si la columna 'status' en BD es booleana (true=Disponible, false=Ocupada)
        // Ajusta según tu esquema real:
        if ($toBusy === 1) {
            $bed->status = false; // Ocupada
        } elseif ($toBusy === 0) {
            $bed->status = true;  // Disponible
        } else {
            // Toggle si no vino explícito
            $bed->status = !(bool)$bed->status;
        }

        $bed->save();

        // Devolvemos el texto que tu JS usa para actualizar la pill
        $statusText = ((bool)$bed->status) ? 'Disponible' : 'Ocupada';

        return response()->json([
            'ok'     => true,
            'status' => $statusText,
        ]);
    }

    public function bulkStore(Request $request)
    {
        // Aquí iría tu guardado masivo de rows[bed_id]...
        // Por ahora dejamos un OK para no romper el flujo.
        return back()->with('success', 'Recolección guardada.');
    }
}
