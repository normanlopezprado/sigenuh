<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffMealController extends Controller
{
    public function create(Request $request)
    {
        // En producción, estos valores vendrán desde tu catálogo en BD
        $dietTypes = [
            'Libre', 'Blanda', 'Hiposódica', 'Diabético 1,200', 'Diabético 1,500',
            'Renal', 'Licuada', 'Especial',
        ];

        // La vista es un mockup (sin lógica de BD real aún)
        return view('staff_meals.create', compact('dietTypes'));
    }

    public function store(Request $request)
    {
        // MOCK: aún no guardamos nada en BD, solo feedback visual
        // Cuando lo conectemos: validar, reautenticar contraseña operador, crear StaffMealRecord, etc.
        return back()->with('success', 'Entrega registrada (mockup).');
    }
}
