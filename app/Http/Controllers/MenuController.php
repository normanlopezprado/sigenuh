<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::latest()->get();
        return view('menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ['desayuno','almuerzo','cena'];
        return view('menus.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:menus,name'],
            'category'     => ['required', Rule::in(['desayuno','almuerzo','cena'])],
            'description' => ['nullable','string'],
            'notes'       => ['nullable','string'],
        ]);

        Menu::create($data);

        return redirect()->route('menus.index')
            ->with('success','Menú creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        // Listado de ingredientes disponibles para agregar
        $ingredients = Ingredient::where('status', true)->orderBy('name')->get();

        // Ingredientes ya asociados (con sus pivotes)
        $current = $menu->ingredients()->orderBy('name')->get();
        $categories = ['desayuno','almuerzo','cena'];
        return view('menus.edit', compact('menu','ingredients','current', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('menus','name')->ignore($menu->id)],
            'category'     => ['required', Rule::in(['desayuno','almuerzo','cena'])],
            'description' => ['nullable','string'],
            'notes'       => ['nullable','string'],

            // Gestión de ingredientes (opcional en el mismo submit)
            'ingredient_id' => ['array'],                 // ids para sync
            'ingredient_id.*' => ['uuid','exists:ingredients,id'],
            'qty' => ['array'],
            'qty.*' => ['nullable','numeric','min:0'],
            'is_optional' => ['array'],
            'is_optional.*' => ['nullable','boolean'],
            'pivot_notes' => ['array'],
            'pivot_notes.*' => ['nullable','string'],
        ]);

        $menu->update($data);

        // Si viene la sección de ingredientes en el mismo form:
        if ($request->has('ingredient_id')) {
            $ids = $request->input('ingredient_id', []);
            $qtys = $request->input('qty', []);
            $opts = $request->input('is_optional', []);
            $notes= $request->input('pivot_notes', []);

            // Construir arreglo para sync con atributos
            $syncPayload = [];
            foreach ($ids as $idx => $ingId) {
                $syncPayload[$ingId] = [
                    // El id de la pivote es opcional; Laravel lo ignora en sync. Si quieres guardarlo, debes crear/actualizar manualmente.
                    'qty'         => isset($qtys[$idx]) ? (float) $qtys[$idx] : 0,
                    'is_optional' => isset($opts[$idx]) ? (bool) $opts[$idx] : false,
                    'notes'       => $notes[$idx] ?? null,
                ];
            }

            // Evita violar unique(menu_id,ingredient_id): sync reemplaza sin duplicar
            $menu->ingredients()->sync($syncPayload);
        }

        return redirect()->route('menus.edit', $menu)
            ->with('success','Menú actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $menu)
    {
        Ingredient::where('id', $menu)->update(['status' => false]);
        return redirect()->route('menus.index')
            ->with('success', 'Ingrediente eliminado correctamente.');
    }
}
