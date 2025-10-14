<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MenuController extends Controller
{

    public function index()
    {
        $menus = Menu::latest()->get();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $diets = [
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
        $dietOptions = array_combine($diets, $diets);
        $categories = ['Desayuno','Almuerzo','Cena'];
        return view('menus.create', compact('categories', 'dietOptions'));
    }

    public function store(Request $request)
    {
        $diets = [
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
        $dietOptions = array_combine($diets, $diets);
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:menus,name'],
            'category'     => ['required', Rule::in(['Desayuno','Almuerzo','Cena'])],
            'diet_type'   => ['nullable', Rule::in($dietOptions)],
            'description' => ['nullable','string'],
            'notes'       => ['nullable','string'],
        ]);

        Menu::create($data);

        return redirect()->route('menus.index')
            ->with('success','Menú creado correctamente.');
    }


    public function show(Menu $menu)
    {
        

    }

    public function edit(Menu $menu)
    {
        $ingredients = Ingredient::where('status', true)->orderBy('name')->get();

        $current = $menu->ingredients()->orderBy('name')->get();
        $categories = ['Desayuno','Almuerzo','Cena'];
        $diets = [
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
        $dietOptions = array_combine($diets, $diets);
        return view('menus.edit', compact('menu','ingredients','current', 'categories', 'dietOptions'));
    }

    public function update(Request $request, Menu $menu)
    {
        $diets = [
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
        $dietOptions = array_combine($diets, $diets);
        $data = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('menus','name')->ignore($menu->id)],
            'category'     => ['required', Rule::in(['Desayuno','Almuerzo','Cena'])],
            'diet_type'   => ['nullable', Rule::in($dietOptions)],
            'description' => ['nullable','string'],
            'notes'       => ['nullable','string'],
            'ingredient_id' => ['array'],                 
            'ingredient_id.*' => ['uuid','exists:ingredients,id'],
            'qty' => ['array'],
            'qty.*' => ['nullable','numeric','min:0'],
            'is_optional' => ['array'],
            'is_optional.*' => ['nullable','boolean'],
            'pivot_notes' => ['array'],
            'pivot_notes.*' => ['nullable','string'],
        ]);

        $menu->update($data);

        if ($request->has('ingredient_id')) {
            $ids = $request->input('ingredient_id', []);
            $qtys = $request->input('qty', []);
            $opts = $request->input('is_optional', []);
            $notes= $request->input('pivot_notes', []);

            $syncPayload = [];
            foreach ($ids as $idx => $ingId) {
                $syncPayload[$ingId] = [
                    'qty'         => isset($qtys[$idx]) ? (float) $qtys[$idx] : 0,
                    'is_optional' => isset($opts[$idx]) ? (bool) $opts[$idx] : false,
                    'notes'       => $notes[$idx] ?? null,
                ];
            }

            $menu->ingredients()->sync($syncPayload);
        }

        return redirect()->route('menus.edit', $menu)
            ->with('success','Menú actualizado correctamente.');
    }

    public function destroy(string $menu)
    {
        Ingredient::where('id', $menu)->update(['status' => false]);
        return redirect()->route('menus.index')
            ->with('success', 'Ingrediente eliminado correctamente.');
    }
}
