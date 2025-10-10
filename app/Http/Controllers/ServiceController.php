<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        
        $services = Service::latest()->get();
        return view('servicios.index', compact('services'));
    }

    public function create()
    {
        return view('servicios.create');
    }

    public function store(Request $request)
    {
        Service::create($request->all()); 
        return redirect()->route('servicios.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    public function show(Service $servicio)
    {
        return view('servicios.show', compact('Service'));
    }

    public function edit(Service $servicio)
    {
        return view('servicios.edit', compact('servicio'));
    }

    public function update(Request $request, Service $servicio)
    {
        $servicio->update($request->all());
        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $servicio)
    {
        $servicio->delete();
        return redirect()->route('servicios.index')
            ->with('success', 'Servicio eliminado.');
    }
}
