<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use App\Models\Cart;
use App\Models\HospitalFloorService;

class CartRouteController extends Controller
{
    
    public function edit(Request $request)
    {
        $hospitals = DB::table('hospitals')
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedHospital = null;
        if ($request->filled('hospital')) {
            $selectedHospital = $hospitals->firstWhere('id', $request->string('hospital'));
        }

        $carts = Cart::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code_name']);

        $selectedCart = null;
        if ($request->filled('cart')) {
            $selectedCart = $carts->firstWhere('id', $request->string('cart'));
        }

        if (!$selectedHospital) {
            return view('carts_routes.route', [
                'hospitals'        => $hospitals,
                'selectedHospital' => null,
                'carts'            => $carts,
                'selectedCart'     => null,
                'available'        => collect(),
                'selected'         => collect(),
            ]);
        }

        if (!$selectedCart) {
            return view('carts_routes.route', [
                'hospitals'        => $hospitals,
                'selectedHospital' => $selectedHospital,
                'carts'            => $carts,
                'selectedCart'     => null,
                'available'        => collect(),
                'selected'         => collect(),
            ]);
        }

        $assignedRows = DB::table('cart_service as cs')
            ->where('cs.cart_id', $selectedCart->id)
            ->join('hospital_floor_services as hfs', 'hfs.id', '=', 'cs.hospital_floor_service_id')
            ->join('hospital_floors as hf', 'hf.id', '=', 'hfs.hospital_floor_id')
            ->where('hf.hospital_id', $selectedHospital->id)
            ->join('nivels as n', 'n.id', '=', 'hf.nivel_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            
            ->orderByRaw("CAST(SUBSTRING_INDEX(n.name, ' ', 1) AS UNSIGNED) DESC")
            ->orderBy('s.name')
            ->get([
                'hfs.id as hfs_id',
                'n.name as floor_name',      
                's.name  as service_name',   
                's.abbreviation as abbr',    
                's.category as category',    
            ]);

        $selectedIds = $assignedRows->pluck('hfs_id')->all();

        $availableRows = HospitalFloorService::query()
            ->from('hospital_floor_services as hfs')
            ->join('hospital_floors as hf', 'hf.id', '=', 'hfs.hospital_floor_id')
            ->where('hf.hospital_id', $selectedHospital->id)
            ->join('nivels as n', 'n.id', '=', 'hf.nivel_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            ->leftJoin('cart_service as cs_any', 'cs_any.hospital_floor_service_id', '=', 'hfs.id')
            ->whereNull('cs_any.id') 
            ->orderByRaw("CAST(SUBSTRING_INDEX(n.name, ' ', 1) AS UNSIGNED) DESC")
            ->orderBy('s.name')
            ->get([
                'hfs.id as hfs_id',
                'n.name as floor_name',
                's.name  as service_name',
                's.abbreviation as abbr',
                's.category as category',
            ]);

        $makeText = function ($floorName, $abbr, $category, $serviceName) {
            $level = trim(strtok($floorName, ' ')) ?: $floorName;

            $abbrTxt = $abbr ?: $serviceName; 
            $catTxt  = $category ?: '';       

            $tail = trim($serviceName . ' ' . $catTxt);

            return sprintf('%s - %s - %s', $level, $abbrTxt, $tail);
        };

        $selected = $assignedRows->map(function ($r) use ($makeText) {
            return (object)[
                'id'   => $r->hfs_id,
                'text' => $makeText($r->floor_name, $r->abbr, $r->category, $r->service_name),
            ];
        });

        $available = $availableRows->map(function ($r) use ($makeText) {
            return (object)[
                'id'   => $r->hfs_id,
                'text' => $makeText($r->floor_name, $r->abbr, $r->category, $r->service_name),
            ];
        });

        return view('carts_routes.route', [
            'hospitals'        => $hospitals,
            'selectedHospital' => $selectedHospital,
            'carts'            => $carts,
            'selectedCart'     => $selectedCart,
            'available'        => $available,
            'selected'         => $selected,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hospital'    => ['required', 'uuid', Rule::exists('hospitals', 'id')],
            'cart'        => ['required', 'uuid', Rule::exists('carts', 'id')],
            'services'    => ['array'],
            'services.*'  => ['uuid', Rule::exists('hospital_floor_services', 'id')],
        ], [
            'hospital.required' => 'Debes seleccionar un hospital.',
            'cart.required'     => 'Debes seleccionar un carrito.',
        ]);

        $hospitalId = $data['hospital'];
        $cartId     = $data['cart'];
        $services   = $data['services'] ?? [];

        if (!empty($services)) {
            $countBelong = DB::table('hospital_floor_services as hfs')
                ->join('hospital_floors as hf', 'hf.id', '=', 'hfs.hospital_floor_id')
                ->where('hf.hospital_id', $hospitalId)
                ->whereIn('hfs.id', $services)
                ->count();

            if ($countBelong !== count($services)) {
                return back()->withErrors('Hay servicios que no pertenecen al hospital seleccionado.')->withInput();
            }

            $conflicts = DB::table('cart_service')
                ->whereIn('hospital_floor_service_id', $services)
                ->where('cart_id', '!=', $cartId)
                ->exists();

            if ($conflicts) {
                return back()->withErrors('Algunos servicios ya están asignados a otro carrito.')->withInput();
            }
        }

        DB::transaction(function () use ($cartId, $services) {
            DB::table('cart_service')->where('cart_id', $cartId)->delete();

            if (empty($services)) return;

            $now    = Carbon::now();
            $userId = optional(auth()->user())->id;

            $rows = [];
            foreach (array_values($services) as $i => $hfsId) {
                $rows[] = [
                    'id'                        => (string) Str::uuid(),
                    'cart_id'                   => $cartId,
                    'hospital_floor_service_id' => $hfsId,
                    'order'                     => $i + 1,
                    'assigned_by'               => $userId,
                    'assigned_at'               => $now,
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ];
            }

            DB::table('cart_service')->insert($rows);
        });

        return redirect()
            ->route('carts.routes.index', ['hospital' => $hospitalId, 'cart' => $cartId])
            ->with('success', 'Ruta del carrito actualizada correctamente.');
    }
}
