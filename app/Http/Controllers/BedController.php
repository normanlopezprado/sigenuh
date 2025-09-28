<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\HospitalFloorService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            ->get() ;

        return view('beds.index', compact('beds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hospitalId = $request->user()->hospital_selected;
        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de crear una cama.');
        }
        $hfs = HospitalFloorService::with(['service', 'hospitalFloor.nivel'])
            ->whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->orderBy(Service::select('name')->whereColumn('services.id', 'hospital_floor_services.service_id'))
            ->get();
        return view('beds.create', compact('hfs'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Bed $bed)
    {
        return view('beds.show', compact('bed'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Bed $bed)
    {
        $hospitalId = $request->user()->hospital_selected;
        if (!$hospitalId) {
            return redirect()->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de editar camas.');
        }

        $hfs = HospitalFloorService::with(['service', 'hospitalFloor.nivel'])
            ->whereHas('hospitalFloor', fn($q) => $q->where('hospital_id', $hospitalId))
            ->orderBy(Service::select('name')->whereColumn('services.id', 'hospital_floor_services.service_id'))
            ->get();

        return view('beds.edit', compact('bed','hfs'));
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bed $bed)
    {
        $bed->delete();

        return redirect()->route('beds.index')
            ->with('success', 'Cama eliminada correctamente.');
    }
}
