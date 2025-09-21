<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ingredients = Ingredient::where('status', true)->latest()->get();;
        return view('ingredients.index', compact('ingredients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = ['g', 'kg', 'ml', 'L', 'unidad'];
        return view('ingredients.create', compact('units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255','unique:ingredients,name'],
            'category' => ['required','string','max:255'],
            'unit'     => ['required', Rule::in(['g','kg','ml','L','unidad'])],
            'notes'    => ['nullable','string'],
        ]);

        Ingredient::create($data);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ingredient $ingredient)
    {
        return view('ingredients.show', compact('ingredient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingredient $ingredient)
    {
        $units = ['g','kg','ml','L','botella', 'galón','unidad'];
        return view('ingredients.edit', compact('ingredient','units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255', Rule::unique('ingredients','name')->ignore($ingredient->id)],
            'category' => ['required','string','max:255'],
            'unit'     => ['required', Rule::in(['g','kg','ml','L','botella', 'galón','unidad'])],
            'notes'    => ['nullable','string'],
        ]);

        $ingredient->update($data);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $ingredient)
    {
        Ingredient::where('id', $ingredient)->update(['status' => false]);
        return redirect()->route('ingredients.index')
            ->with('success', 'Ingrediente eliminado correctamente.');
    }
}
