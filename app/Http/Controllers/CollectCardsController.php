<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Collect;
use App\Models\Hospital;
use App\Models\HospitalFloorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CollectCardsController extends Controller
{

    private const VALID_DIETS = [
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

    private function dietsFromDB(): array
    {
        try {
            $col  = DB::selectOne("SHOW COLUMNS FROM `collects` WHERE Field = 'diet_type'");
            $type = $col?->Type ?? ''; 
            if (preg_match('/^enum\((.*)\)$/i', $type, $m)) {
            
                $vals = str_getcsv($m[1], ',', "'");
                return array_map('trim', $vals);
            }
        } catch (\Throwable $e) {
            
        }
        return self::VALID_DIETS;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $hospitalId = $user->hospital_selected;

        if (!$hospitalId) {
            return redirect()->route('dashboard')->with('warning', 'Selecciona un hospital.');
        }

        $hospital = Hospital::findOrFail($hospitalId);

        $dateStr = $request->input('date');
        try {
            $date = $dateStr ? Carbon::parse($dateStr)->toDateString() : Carbon::now()->toDateString();
        } catch (\Throwable $e) {
            $date = Carbon::now()->toDateString();
        }

        $tz  = config('app.timezone', 'America/Guatemala');

        $now = Carbon::now($tz);
        $now = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} ".$now->format('H:i:s'), $tz);

        $mkDateTime = function ($t) use ($date, $tz) {
            if ($t === null) return null;
            $t = trim((string)$t);
            if ($t === '') return null;

            // Si ya viene con fecha
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $t)) {
                try { return Carbon::parse($t, $tz); } catch (\Throwable $e) { return null; }
            }

            if (preg_match('/^\d{1,2}:\d{2}$/', $t)) $t .= ':00';
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$t}", $tz);
            } catch (\Throwable $e) {
                try { return Carbon::parse("{$date} {$t}", $tz); } catch (\Throwable $e2) { return null; }
            }
        };

        $inWindow = function (Carbon $now, ?Carbon $start, ?Carbon $end) {
            if (!$start || !$end) return false;
            if ($end->lessThanOrEqualTo($start)) { 
                $end = $end->copy()->addDay();
                if ($now->lessThan($start)) $now = $now->copy()->addDay();
            }
            return $now->betweenIncluded($start, $end);
        };

        $fmt12 = function (?Carbon $dt) {
            return $dt ? $dt->format('g:i a') : null;
        };

        $Bstart = $mkDateTime($hospital->breakfast_collection_start);
        $Bend   = $mkDateTime($hospital->breakfast_collection_end);
        $Lstart = $mkDateTime($hospital->lunch_collection_start);
        $Lend   = $mkDateTime($hospital->lunch_collection_end);
        $Dstart = $mkDateTime($hospital->dinner_collection_start);
        $Dend   = $mkDateTime($hospital->dinner_collection_end);

        $windows = [
            'Desayuno' => ['start' => $Bstart, 'end' => $Bend],
            'Almuerzo' => ['start' => $Lstart, 'end' => $Lend],
            'Cena'     => ['start' => $Dstart, 'end' => $Dend],
        ];

        $active = null;
        foreach ($windows as $label => $w) {
            if ($inWindow($now, $w['start'], $w['end'])) { $active = $label; break; }
        }

        $nextLabel = null; $etaText = null;
        if (!$active) {
            $valid = collect($windows)
                ->filter(fn($w) => $w['start'] && $w['end'])
                ->map(fn($w, $label) => ['label'=>$label, 'start'=>$w['start']])
                ->sortBy('start')
                ->values();

            if ($valid->isNotEmpty()) {
                $next = $valid->first(fn($x) => $x['start']->greaterThanOrEqualTo($now))
                    ?? ['label'=>$valid->first()['label'], 'start'=>$valid->first()['start']->copy()->addDay()];
                $nextLabel = $next['label'];
                $mins = $now->diffInMinutes($next['start'], false);
                if     ($mins <= 0) $etaText = 'en breve';
                elseif ($mins < 60) $etaText = "en {$mins} min";
                else { $h=intdiv($mins,60); $m=$mins%60; $etaText = $m ? "en {$h} h {$m} min" : "en {$h} h"; }
            }
        }

        $meal   = $active ?? ($request->input('meal') ?: 'Desayuno');
        $isOpen = (bool) $active;

        $windowMessage = $active
        ? "Ventana activa: {$active}"
        : ($nextLabel ? "Fuera de horario de recolección — siguiente: {$nextLabel} {$etaText}"
                    : "Fuera de horario de recolección");


        $windowConfig = [
            'Desayuno' => ($Bstart && $Bend) ? ($fmt12($Bstart).' - '.$fmt12($Bend)) : null,
            'Almuerzo' => ($Lstart && $Lend) ? ($fmt12($Lstart).' - '.$fmt12($Lend)) : null,
            'Cena'     => ($Dstart && $Dend) ? ($fmt12($Dstart).' - '.$fmt12($Dend)) : null,
        ];

        $services = HospitalFloorService::query()
            ->with(['hospitalFloor.nivel','service'])
            ->whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->get()
            ->map(function ($hfs) {
                $nivelName = $hfs->hospitalFloor?->nivel?->name;
                $floorNum = 0;
                if (is_string($nivelName) && preg_match('/\d+/', $nivelName, $m)) {
                    $floorNum = (int)$m[0];
                } else {
                    $floorNum = -1; 
                }

                $abbr = $hfs->service?->abbreviation;
                $name = $hfs->service?->name;
                $cat  = $hfs->service?->category;

                $label = trim(collect([$nivelName, $abbr, "{$name} {$cat}"])
                    ->filter()
                    ->implode(' - '));

                return [
                    'id'     => $hfs->id,
                    'label'  => $label,
                    '_floor' => $floorNum,
                    '_name'  => strtolower($name ?? ''),
                ];
            })
            ->sortBy([['_floor','desc'],['_name','asc']])
            ->values()
            ->map(fn($row) => ['id'=>$row['id'], 'label'=>$row['label']]);

        $hfsId   = $request->input('service');
        $beds    = collect();
        $prefill = [];

        if ($hfsId) {
            $beds = Bed::query()
                ->with(['hospitalFloorService.service','hospitalFloorService.hospitalFloor.nivel'])
                ->where('hospital_floor_service_id', $hfsId)
                ->orderBy('code')
                ->get();

            if ($beds->isNotEmpty()) {
                $collects = Collect::query()
                    ->whereIn('bed_id', $beds->pluck('id')->all())
                    ->whereDate('date', $date)
                    ->where('meal', $meal)
                    ->get()
                    ->keyBy('bed_id');

                $prefill = $collects->map(fn($c) => [
                    'id'                   => $c->id,
                    'diet_type'            => $c->diet_type,
                    'trays_count'          => $c->trays_count,
                    'disposables_count'    => $c->disposables_count,
                    'notes'                => $c->notes,
                    'has_minor'            => (bool)$c->has_minor,
                    'minor_age'            => $c->minor_age,
                    'has_companion'        => (bool)$c->has_companion,
                    'companion_diet_type'  => $c->companion_diet_type,
                    'companion_notes'      => $c->companion_notes,
                    'has_disponsable'      => (bool)$c->has_disponsable,
                ])->toArray();
            }
        }

        if (empty(array_filter($windowConfig))) {
            $to12 = function ($t) { if (!$t) return null;
                try { return Carbon::parse($t)->format('g:i a'); } catch (\Throwable $e) { return null; }
            };
            $windowConfig = [
                'Desayuno' => ($hospital?->breakfast_collection_start && $hospital?->breakfast_collection_end)
                    ? ($to12($hospital->breakfast_collection_start).' - '.$to12($hospital->breakfast_collection_end)) : null,
                'Almuerzo' => ($hospital?->lunch_collection_start && $hospital?->lunch_collection_end)
                    ? ($to12($hospital->lunch_collection_start).' - '.$to12($hospital->lunch_collection_end)) : null,
                'Cena'     => ($hospital?->dinner_collection_start && $hospital?->dinner_collection_end)
                    ? ($to12($hospital->dinner_collection_start).' - '.$to12($hospital->dinner_collection_end)) : null,
            ];
        }

        $diets = $this->dietsFromDB();

        return view('collects.cards', [
            'hospitalId'      => $hospitalId,
            'services'        => $services,
            'selectedService' => $hfsId,
            'date'            => $date,
            'meal'            => $meal,
            'beds'            => $beds,
            'prefill'         => $prefill,
            'isOpen'          => $isOpen,
            'windowMessage'   => $windowMessage,
            'windowConfig'    => $windowConfig,
            'diets'           => $diets,
        ]);
    }

    public function toggleBed(Request $request, Bed $bed)
    {
        $toBusy = (int) ($request->input('to_busy') ?? 0);
        $status = $toBusy ? 'Ocupada' : 'Disponible';

        $bed->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'status' => $status]);
    }

    public function upsert(Request $request, Bed $bed)
    {
        $validDiets = $this->dietsFromDB();

        $dateStr = $request->input('date');
        try {
            $date = $dateStr ? Carbon::parse($dateStr)->toDateString() : Carbon::now()->toDateString();
        } catch (\Throwable $e) {
            $date = Carbon::now()->toDateString();
        }
        $meal = $request->input('meal') ?: 'Desayuno';

        $data = $request->validate([
            'diet_type'           => ['nullable', Rule::in($validDiets)],
            'trays_count'         => ['nullable','integer','min:0'],
            'disposables_count'   => ['nullable','integer','min:0'],
            'notes'               => ['nullable','string','max:2000'],
            'has_minor'           => ['nullable','boolean'],
            'minor_age'           => ['nullable','integer','min:0','max:120'],
            'has_companion'       => ['nullable','boolean'],
            'companion_diet_type' => ['nullable', Rule::in($validDiets)],
            'companion_notes'     => ['nullable','string','max:2000'],
        ]);

        $data['trays_count']       = $data['trays_count']       ?? 0;
        $data['disposables_count'] = $data['disposables_count'] ?? 0;
        $data['has_minor']         = (int)($data['has_minor']   ?? 0);
        $data['has_companion']     = (int)($data['has_companion'] ?? 0);

        $collect = Collect::updateOrCreate(
            [
                'bed_id' => $bed->id,
                'date'   => $date,
                'meal'   => $meal,
            ],
            [
                'diet_type'            => $data['diet_type'] ?? null,
                'trays_count'          => $data['trays_count'],
                'disposables_count'    => $data['disposables_count'],
                'notes'                => $data['notes'] ?? null,
                'has_minor'            => $data['has_minor'],
                'minor_age'            => $data['has_minor'] ? ($data['minor_age'] ?? null) : null,
                'has_companion'        => $data['has_companion'],
                'companion_diet_type'  => $data['has_companion'] ? ($data['companion_diet_type'] ?? null) : null,
                'companion_notes'      => $data['companion_notes'] ?? null,
                'user_id'              => optional($request->user())->id,
            ]
        );

        return response()->json([
            'ok'                  => true,
            'collect_id'          => $collect->id,
            'has_minor'           => (bool)$collect->has_minor,
            'has_companion'       => (bool)$collect->has_companion,
            'companion_diet_type' => $collect->companion_diet_type,
        ]);
    }

    public function bulkUpsert(Request $request)
    {
        $user = $request->user();
        $hospitalId = $user->hospital_selected;
        if (!$hospitalId) {
            return back()->with('warning','Selecciona un hospital.');
        }

        $validDiets = $this->dietsFromDB();

        $data = $request->validate([
            'date'  => ['required','date'],
            'meal'  => ['required','in:Desayuno,Almuerzo,Cena'],
            'rows'  => ['required','array'],

            'rows.*.__touched'           => ['nullable','in:0,1'],
            'rows.*.diet_type'           => ['nullable', Rule::in($validDiets)],
            'rows.*.has_disponsable'     => ['nullable','in:0,1'],
            'rows.*.has_minor'           => ['nullable','in:0,1'],
            'rows.*.minor_age'           => ['nullable','integer','min:0','max:120'],
            'rows.*.has_companion'       => ['nullable','in:0,1'],
            'rows.*.companion_diet_type' => ['nullable', Rule::in($validDiets)],
            
        ]);

        $date = $data['date'];
        $meal = $data['meal'];
        $rows = $request->input('rows', []);
        if (!is_array($rows) || empty($rows)) {
            return back()->with('warning','No se recibieron datos para guardar.');
        }

        $changed = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $bedId => $row) {

                $touched = (string)($row['__touched'] ?? '0') === '1';

                $diet     = isset($row['diet_type']) ? trim((string)$row['diet_type']) : '';
                $hasDisp  = (int)($row['has_disponsable'] ?? 0);
                $hasMinor = (int)($row['has_minor'] ?? 0);
                $minorAge = $row['minor_age'] ?? null;
                $minorAge = ($minorAge === '' || $minorAge === null) ? null : (int)$minorAge;

                $hasComp  = (int)($row['has_companion'] ?? 0);
                $compDiet = isset($row['companion_diet_type']) ? trim((string)$row['companion_diet_type']) : '';

                $belongs = Bed::query()
                    ->where('id', $bedId)
                    ->whereHas('hospitalFloorService.hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
                    ->exists();
                if (!$belongs) continue;

                $existing = Collect::where('bed_id', $bedId)
                    ->whereDate('date', $date)
                    ->where('meal', $meal)
                    ->first();

                $isEmptyPayload =
                    ($diet === '') &&
                    ($hasDisp === 0) &&
                    ($hasMinor === 0) &&
                    ($hasComp === 0);

                if (!$touched && $isEmptyPayload && !$existing) continue;

                if ($existing) {
                    $same =
                        ((string)($existing->diet_type ?? '') === $diet) &&
                        ((int)($existing->has_disponsable ?? 0) === $hasDisp) &&
                        ((int)($existing->has_minor ?? 0) === $hasMinor) &&
                        ((int)($existing->minor_age ?? 0) === (int)($minorAge ?? 0)) &&
                        ((int)($existing->has_companion ?? 0) === $hasComp) &&
                        ((string)($existing->companion_diet_type ?? '') === $compDiet);

                    if ($same) continue; 
                } else {

                    if ($isEmptyPayload) continue;
                }


                Collect::updateOrCreate(
                    [
                        'bed_id' => $bedId,
                        'date'   => $date,
                        'meal'   => $meal,
                    ],
                    [
                        'diet_type'            => ($diet !== '') ? $diet : null,
                        'has_disponsable'      => $hasDisp,
                        'has_minor'            => $hasMinor,
                        'minor_age'            => $hasMinor ? $minorAge : null,
                        'has_companion'        => $hasComp,
                        'companion_diet_type'  => ($hasComp && $compDiet !== '') ? $compDiet : null,
                        'user_id'              => $user->id,
                    ]
                );

                $changed++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error','No se pudieron guardar los datos: '.$e->getMessage());
        }

        if ($changed === 0) {
            return back()->with('warning','No hubo cambios para guardar.');
        }

        return back()->with('success',"Se guardaron {$changed} cambio(s).");
    }
}
