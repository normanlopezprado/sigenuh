<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Calendar;
use App\Models\Hospital;
use App\Models\HospitalFloorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollectCardsController extends Controller
{
    public function cards(Request $request)
    {
 
        $serviceId = $request->input('service'); 
        $date      = $request->input('date') ?: now()->toDateString();

        
        $services = $this->getServiceOptionsForSelect();

        
        $beds = collect();
        $collectsByBed = [];
        if ($serviceId) {
            $beds = Bed::with([
                'hospitalFloorService.service:id,name,abbreviation,category'
            ])
            ->where('hospital_floor_service_id', $serviceId)
            ->orderBy('code')
            ->get();

            $collectsByBed = DB::table('collects')
                ->whereDate('date', $date)
                ->whereIn('bed_id', $beds->pluck('id'))
                ->get()
                ->keyBy('bed_id')
                ->map(function ($row) {
                    return (array) $row;
                })
                ->toArray();
        }

        
        $hospital = $serviceId ? $this->resolveHospitalFromService($serviceId) : null;

        $windows = $hospital ? $this->getCollectionWindows($hospital) : [
            'Desayuno' => ['start'=>null,'end'=>null],
            'Almuerzo' => ['start'=>null,'end'=>null],
            'Cena'     => ['start'=>null,'end'=>null],
        ];

        $tz  = config('app.timezone', 'America/Guatemala'); 
        $now = Carbon::now($tz);

        
        [$meal, $isOpen] = $this->detectActiveMealFromWindows($windows, $now);

        
        if (!$meal) {
        
            $firstMenuCat = Calendar::query()
                ->with(['menu:id,category'])
                ->whereDate('date', $date)
                ->orderBy('id')
                ->get()
                ->pluck('menu.category')
                ->filter()
                ->first();

            $meal = $firstMenuCat ?: '—';
            $isOpen = false;
        }

        
        $diets = $this->getDietList();

        
        return view('collects.cards', [
            'date'           => $date,
            'meal'           => $meal,
            'isOpen'         => $isOpen,
            'serviceId'      => $serviceId,
            'services'       => $services,
            'beds'           => $beds,
            'collectsByBed'  => $collectsByBed,
            'diets'          => $diets,
        ]);
    }


    public function bulk(Request $request)
    {
        $request->validate([
            'service' => ['required','string'],
            'date'    => ['nullable','date'],
            'rows'    => ['required','array'],
        ]);

        $serviceId = $request->input('service');
        $date      = $request->input('date') ?: now()->toDateString();

        $hospital = $this->resolveHospitalFromService($serviceId);
        if (!$hospital) {
            return back()->with('error', 'No se pudo determinar el hospital del servicio seleccionado.');
        }

        $windows = $this->getCollectionWindows($hospital);

        $tz  = config('app.timezone', 'America/Guatemala');
        $now = Carbon::now($tz);

        [$meal, $isOpen] = $this->detectActiveMealFromWindows($windows, $now);

        if (!$meal || !$isOpen) {
            return back()->with('error', 'Fuera de la ventana de recolección. Verifica los horarios.')->withInput();
        }

        $rows  = $request->input('rows', []);
        $userId = $request->user()?->id;

        try {
            DB::beginTransaction();

            foreach ($rows as $bedId => $row) {
                if (empty($row['__present'])) continue; 

                $payload = [
                    'id'                  => (string) Str::uuid(),
                    'bed_id'              => $bedId,
                    'date'                => $date,
                    'meal'                => $meal,
                    'diet_type'           => $row['diet_type']          ?? null,
                    'has_disponsable'     => !empty($row['has_disponsable']) ? 1 : 0,
                    'has_minor'           => !empty($row['has_minor']) ? 1 : 0,
                    'minor_age'           => isset($row['minor_age']) && $row['minor_age'] !== '' ? (int)$row['minor_age'] : null,
                    'has_companion'       => !empty($row['has_companion']) ? 1 : 0,
                    'companion_diet_type' => $row['companion_diet_type'] ?? null,
                    'user_id'             => $userId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];

                
                $existing = DB::table('collects')
                    ->whereDate('date', $date)
                    ->where('bed_id', $bedId)
                    ->where('meal', $meal)
                    ->first();

                if ($existing) {
                    unset($payload['id'], $payload['created_at']);
                    DB::table('collects')->where('id', $existing->id)->update($payload);
                } else {
                    DB::table('collects')->insert($payload);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Ocurrió un error al guardar: '.$e->getMessage())->withInput();
        }

        return redirect()->route('collects.cards', ['service' => $serviceId])->with('success', 'Recolección guardada correctamente.');
    }


    private function resolveHospitalFromService(string $hospitalFloorServiceId): ?Hospital
    {

        $hfs = HospitalFloorService::with([
            'hospitalFloor:id,hospital_id',
            'hospitalFloor.hospital:id,name,breakfast_collection_start,breakfast_collection_end,breakfast_time,lunch_collection_start,lunch_collection_end,lunch_time,dinner_collection_start,dinner_collection_end,dinner_time',
        ])->find($hospitalFloorServiceId);

        return $hfs?->hospitalFloor?->hospital;
    }

    private function getCollectionWindows(Hospital $hospital): array
    {
        return [
            'Desayuno' => [
                'start' => $hospital->breakfast_collection_start,
                'end'   => $hospital->breakfast_collection_end,
            ],
            'Almuerzo' => [
                'start' => $hospital->lunch_collection_start,
                'end'   => $hospital->lunch_collection_end,
            ],
            'Cena' => [
                'start' => $hospital->dinner_collection_start,
                'end'   => $hospital->dinner_collection_end,
            ],
        ];
    }


    private function normalizeTime(?string $t): ?string
    {
        if (!$t) return null;
        $t = trim($t);
        if (preg_match('/^\d{2}:\d{2}$/', $t)) return $t . ':00';
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $t)) return $t;
        return null;
    }


    private function toCarbonTime(Carbon $base, ?string $t): ?Carbon
    {
        $n = $this->normalizeTime($t);
        if (!$n) return null;
        [$H,$i,$s] = explode(':', $n);
        return $base->copy()->setTime((int)$H,(int)$i,(int)$s);
    }


    private function isNowInWindow(Carbon $now, ?string $start, ?string $end): bool
    {
        $startC = $this->toCarbonTime($now, $start);
        $endC   = $this->toCarbonTime($now, $end);
        if (!$startC || !$endC) return false;


        if ($endC->lt($startC)) {
            return $now->gte($startC) || $now->lte($endC->copy()->addDay());
        }


        return $now->gte($startC) && $now->lte($endC);
    }


    private function detectActiveMealFromWindows(array $windows, Carbon $now): array
    {

        foreach (['Desayuno','Almuerzo','Cena'] as $cat) {
            $w = $windows[$cat] ?? null;
            if (!$w) continue;

            $start = $this->normalizeTime($w['start'] ?? null);
            $end   = $this->normalizeTime($w['end']   ?? null);

            if ($start && $end && $this->isNowInWindow($now, $start, $end)) {
                return [$cat, true];
            }
        }
        return [null, false];
    }

    private function getDietList(): array
    {

        return ['Libre','Blanda','Hipo sódica','Diabética','Líquidos','Sin Glúten'];
    }


    private function getServiceOptionsForSelect()
    {
        $list = \App\Models\HospitalFloorService::query()
            ->with([
                'hospitalFloor:id,nivel_id,hospital_id',
                'hospitalFloor.nivel:id,name', 
                'service:id,name,abbreviation,category',
            ])
            
            ->join('hospital_floors', 'hospital_floors.id', '=', 'hospital_floor_services.hospital_floor_id')
            ->join('nivels', 'nivels.id', '=', 'hospital_floors.nivel_id')
            ->join('services', 'services.id', '=', 'hospital_floor_services.service_id')
            ->orderByRaw('CAST(SUBSTRING_INDEX(nivels.name, "to", 1) AS UNSIGNED) DESC') 
            ->orderBy('services.name')
            ->select('hospital_floor_services.*')
            ->get();

        return $list->map(function ($hfs) {
            $nivelName = $hfs->hospitalFloor?->nivel?->name ?? '';
            $abbr      = $hfs->service?->abbreviation ?? '';
            $name      = $hfs->service?->name ?? '';
            $cat       = $hfs->service?->category ?? '';
            $label     = trim("{$nivelName} -- {$abbr} -- {$name} {$cat}");
            return (object)[
                'id'             => $hfs->id,
                'display_levels' => $nivelName,
                'abbreviation'   => $abbr,
                'name'           => $name,
                'category'       => $cat,
                'label'          => $label,
            ];
        });
    }
    
    public function toggleBed(Request $request, \App\Models\Bed $bed)
    {
        $toBusy = (int) ($request->input('to_busy') ?? 0); 
        $status = $toBusy ? 'Ocupada' : 'Disponible';

        $bed->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'status' => $status]);
    }
}
