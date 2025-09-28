<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngredientController extends Controller
{
    // Catálogo fijo de categorías
    private const CATEGORIES = ['Condimento','Fruta','Proteína','Verdura'];

    // Valores EXACTOS del ENUM => etiqueta visible
    private const UNITS = [
        'g.'   => 'g. — gramo',
        'lb.'  => 'lb. — libra',
        'ml.'  => 'ml. — mililitro',
        'L.'   => 'L. — litro',
        'gal.' => 'gal. — galón',
        'ud.'  => 'ud. — unidad',
    ];

    public function index()
    {
        $ingredients = Ingredient::where('status', true)->latest()->get();
        return view('ingredients.index', compact('ingredients'));
    }

    public function create()
    {
        $categories = self::CATEGORIES;
        $unitsMap   = self::UNITS;
        return view('ingredients.create', compact('categories','unitsMap'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255','unique:ingredients,name'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'unit'     => ['required', Rule::in(array_keys(self::UNITS))], // <-- usa ENUM exacto
            'notes'    => ['nullable','string'],
        ]);

        Ingredient::create($data);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente creado correctamente.');
    }

    public function show(Ingredient $ingredient)
    {
        return view('ingredients.show', compact('ingredient'));
    }

    public function edit(Ingredient $ingredient)
    {
        $categories = self::CATEGORIES;
        $unitsMap   = self::UNITS;
        return view('ingredients.edit', compact('ingredient','categories','unitsMap'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255', Rule::unique('ingredients','name')->ignore($ingredient->id)],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'unit'     => ['required', Rule::in(array_keys(self::UNITS))],
            'notes'    => ['nullable','string'],
        ]);

        $ingredient->update($data);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente actualizado correctamente.');
    }

    public function destroy(string $ingredient)
    {
        Ingredient::where('id', $ingredient)->update(['status' => false]);
        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente eliminado correctamente.');
    }
}
