<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{

    public function index()
    {
        $niveles  = Nivel::where('status', true)->latest()->get();
        return view('niveles.index', compact('niveles'));
    }

    public function create()
    {
        return view('niveles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:100']
        ]);
        Nivel::create($data);
        return redirect()->route('niveles.index')
            ->with('success','Nivel creado correctamente.');
    }

    public function show(Nivel $nivel)
    {
        return view('niveles.show', ['nivel' => $nivel]);
    }

    public function edit(Nivel $nivel)
    {
        return view('niveles.edit', ['nivel' => $nivel]);
    }

    public function update(Request $request, Nivel $nivel)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:100']
        ]);

        $nivel->update($data);

        return redirect()->route('niveles.index')
            ->with('success','Nivel actualizado correctamente.');
    }

    public function destroy(string $nivel)
    {
        Nivel::where('id', $nivel)->update(['status' => false]);
        return redirect()->route('niveles.index')
            ->with('success', 'Nivel desactivado.');
    }
}
