<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\HospitalFloorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BedController extends Controller
{
    public function index(Request $request)
    {
        $hospitalId = $request->user()->hospital_selected;

        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de ver las camas.');
        }

        $beds = Bed::with([
            'hospitalFloorService.service',
            'hospitalFloorService.hospitalFloor.hospital',
            'hospitalFloorService.hospitalFloor.nivel',
        ])
            ->whereHas('hospitalFloorService.hospitalFloor', function ($q) use ($hospitalId) {
                $q->where('hospital_id', $hospitalId);
            })
            ->latest()
            ->get();

        return view('beds.index', compact('beds'));
    }

    public function create(Request $request)
    {
        $hospitalId = $request->user()->hospital_selected;
        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de crear una cama.');
        }

        $hfs = HospitalFloorService::with(['service', 'hospitalFloor.nivel'])
            ->whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->get()
            ->sortByDesc(function ($x) {
                $level = (int) preg_replace('/\D+/', '', $x->hospitalFloor?->nivel?->name ?? '0');
                $serviceName = mb_strtolower($x->service?->name ?? '');
                return sprintf('%05d-%s', $level, $serviceName);
            })
            ->values();

        return view('beds.create', compact('hfs'));
    }

    public function store(Request $request)
    {
        $hospitalId = $request->user()->hospital_selected;
        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de crear una cama.');
        }

        $allowedIds = HospitalFloorService::whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->pluck('id')->toArray();

        $data = $request->validate([
            'code'   => ['required','string','max:50'],
            'status' => ['required','in:Disponible,Ocupada,Mantenimiento'],
            'notes'  => ['nullable','string'],
            'hospital_floor_service_id' => ['required','uuid', Rule::in($allowedIds)],
        ]);

        Bed::create($data);

        return redirect()->route('beds.index')->with('success','Cama creada correctamente.');
    }

    public function show(Bed $bed)
    {
        return view('beds.show', compact('bed'));
    }

    public function edit(Request $request, Bed $bed)
    {
        $hospitalId = $request->user()->hospital_selected;
        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de editar camas.');
        }

        $hfs = HospitalFloorService::with(['service', 'hospitalFloor.nivel'])
            ->whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->get()
            ->sortByDesc(function ($x) {
                $level = (int) preg_replace('/\D+/', '', $x->hospitalFloor?->nivel?->name ?? '0');
                $serviceName = mb_strtolower($x->service?->name ?? '');
                return sprintf('%05d-%s', $level, $serviceName);
            })
            ->values();

        return view('beds.edit', compact('bed','hfs'));
    }

    public function update(Request $request, Bed $bed)
    {
        $hospitalId = $request->user()->hospital_selected;
        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de editar camas.');
        }

        $allowedIds = HospitalFloorService::whereHas('hospitalFloor', fn($q) =>
            $q->where('hospital_id', $hospitalId)
        )->pluck('id')->toArray();

        $data = $request->validate([
            'code'   => ['required','string','max:50'],
            'status' => ['required','in:Disponible,Ocupada,Mantenimiento'],
            'notes'  => ['nullable','string'],
            'hospital_floor_service_id' => ['required','uuid', Rule::in($allowedIds)],
        ]);

        $bed->update($data);

        return redirect()->route('beds.index')->with('success','Cama actualizada correctamente.');
    }

    public function destroy(Bed $bed)
    {
        $bed->delete();

        return redirect()->route('beds.index')
            ->with('success', 'Cama eliminada correctamente ');
    }
}
