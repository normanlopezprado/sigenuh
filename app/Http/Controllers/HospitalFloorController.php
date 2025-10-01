<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Nivel;
use Illuminate\Http\Request;
class HospitalFloorController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $hospital = $user->hospital_selected;

        if (!$hospital) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de administrar los niveles.');
        }

        $niveles = Nivel::where('status', true)->get()
            ->sortBy(fn ($n) => (int) preg_replace('/\D+/', '', $n->name ?? ''))
            ->reverse()
            ->values()
        ;

        $hospital = Hospital::where('id', $hospital)->first();
        $seleccionados = $hospital->niveles()->pluck('nivels.id')->toArray();
        return view('hospital_floors.edit', compact('hospital','niveles','seleccionados'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $hospital = $user->hospital_selected;
        if (!$hospital) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Selecciona un hospital antes de administrar los niveles.');
        }

        $data = $request->validate([
            'niveles'   => ['array'],
            'niveles.*' => ['uuid','exists:nivels,id'],
        ]);

        $ids = $data['niveles'] ?? [];

        $hospital = Hospital::where('id', $hospital)->first();
        $hospital->niveles()->sync($ids);
            return redirect()->route('hospital-floors.edit')->with('success', 'Niveles asignados correctamente.');
    }
}
