<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HospitalController extends Controller
{

    public function index()
    {
        $hospitals = Hospital::latest()->paginate(10);
        return view('hospitales.index', compact('hospitals'));
    }

    public function create()
    {
        return view('hospitales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:120'],
            'description' => ['nullable','string'],
            'address'     => ['nullable','string','max:255'],
            'email'       => ['nullable','string','max:255'],
            'phone'       => ['nullable','string','max:10'],
            'logo'        => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'icon'        => ['nullable','mimes:png,webp,ico','max:256'],
            'latitude'    => ['nullable','numeric','between:-90,90'],
            'longitude'   => ['nullable','numeric','between:-180,180'],
            'breakfast_collection_start' => ['nullable','date_format:H:i'],
            'breakfast_collection_end'   => ['nullable','date_format:H:i'],
            'breakfast_time'             => ['nullable','date_format:H:i'],
            'lunch_collection_start'     => ['nullable','date_format:H:i'],
            'lunch_collection_end'       => ['nullable','date_format:H:i'],
            'lunch_time'                 => ['nullable','date_format:H:i'],
            'dinner_collection_start'    => ['nullable','date_format:H:i'],
            'dinner_collection_end'      => ['nullable','date_format:H:i'],
            'dinner_time'                => ['nullable','date_format:H:i'],
        ]);
        $uuid = (string) Str::uuid();
        $data['id'] = $uuid; 
        if ($request->hasFile('logo')) {
            $ext = $request->file('logo')->extension();
            $filename = "{$uuid}.{$ext}";
            $request->file('logo')->storeAs('logos', $filename, 'public');
            $data['logo_path'] = "logos/{$filename}";
        }

        if ($request->hasFile('icon')) {
            $ext = $request->file('icon')->extension();
            $filename = "{$uuid}.{$ext}";
            $request->file('icon')->storeAs('icons', $filename, 'public');
            $data['icon_path'] = "icons/{$filename}";
        }
        $hospital = Hospital::create($data);

        return redirect()->route('hospitales.index', $hospital)
            ->with('success', 'Hospital creado correctamente.');
    }

    public function show(Hospital $hospital)
    {
        return view('hospitales.show', compact('hospital'));
    }


    public function edit(Hospital $hospital)
    {
        return view('hospitales.edit', compact('hospital'));
    }


    public function update(Request $request, Hospital $hospital)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:120'],
            'description' => ['nullable','string'],
            'address'     => ['nullable','string','max:255'],
            'email'       => ['nullable','string','max:255'],
            'phone'       => ['nullable','string','max:10'],
            'logo'        => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'icon'        => ['nullable','mimes:png,webp,ico','max:256'],
            'latitude'    => ['nullable','numeric','between:-90,90'],
            'longitude'   => ['nullable','numeric','between:-180,180'],
            'breakfast_collection_start' => ['nullable','date_format:H:i'],
            'breakfast_collection_end'   => ['nullable','date_format:H:i'],
            'breakfast_time'             => ['nullable','date_format:H:i'],
            'lunch_collection_start'     => ['nullable','date_format:H:i'],
            'lunch_collection_end'       => ['nullable','date_format:H:i'],
            'lunch_time'                 => ['nullable','date_format:H:i'],
            'dinner_collection_start'    => ['nullable','date_format:H:i'],
            'dinner_collection_end'      => ['nullable','date_format:H:i'],
            'dinner_time'                => ['nullable','date_format:H:i'],
        ]);
        $uuid = $hospital->id; 

        if ($request->hasFile('logo')) {
            if ($hospital->logo_path) {
                Storage::disk('public')->delete($hospital->logo_path);
            }
            $ext = $request->file('logo')->extension();
            $filename = "{$uuid}.{$ext}";
            $request->file('logo')->storeAs('logos', $filename, 'public');
            $data['logo_path'] = "logos/{$filename}";
        }

        if ($request->hasFile('icon')) {
            if ($hospital->icon_path) { 
                Storage::disk('public')->delete($hospital->icon_path);
            }
            $ext = $request->file('icon')->extension();
            $filename = "{$uuid}.{$ext}";
            $request->file('icon')->storeAs('icons', $filename, 'public');
            $data['icon_path'] = "icons/{$filename}";
        }
        $hospital->update($data);
        return redirect()->route('hospitales.index', $hospital)
            ->with('success', 'Hospital actualizado correctamente.');
    }

    public function destroy(Hospital $hospital)
    {
        if ($hospital->logo_path) {
            Storage::disk('public')->delete($hospital->logo_path);
        }
        $hospital->delete();

        return redirect()->route('hospitales.index')
            ->with('success', 'Hospital eliminado.');
    }
}
