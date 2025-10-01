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
    /**
     * Flujo:
     * 1) Seleccionar hospital
     * 2) Seleccionar carrito
     * 3) Dual-list: disponibles (no asignados a ningún carrito) ↔ asignados (de este carrito)
     *
     * Listas ordenadas DESC por nivel (primer token numérico de nivels.name).
     * Texto de ítems: "5to - {abbr} - {service_name} {category}".
     */
    public function edit(Request $request)
    {
        // --- HOSPITALES ---
        $hospitals = DB::table('hospitals')
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedHospital = null;
        if ($request->filled('hospital')) {
            $selectedHospital = $hospitals->firstWhere('id', $request->string('hospital'));
        }

        // --- CARRITOS ---
        $carts = Cart::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code_name']);

        $selectedCart = null;
        if ($request->filled('cart')) {
            $selectedCart = $carts->firstWhere('id', $request->string('cart'));
        }

        // Si no hay hospital todavía, muestra solo select de hospital
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

        // Si hay hospital pero no carrito, muestra selects y no el dual-list
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

        // --- ASIGNADOS (del carrito seleccionado), limitados al hospital elegido ---
        $assignedRows = DB::table('cart_service as cs')
            ->where('cs.cart_id', $selectedCart->id)
            ->join('hospital_floor_services as hfs', 'hfs.id', '=', 'cs.hospital_floor_service_id')
            ->join('hospital_floors as hf', 'hf.id', '=', 'hfs.hospital_floor_id')
            ->where('hf.hospital_id', $selectedHospital->id)
            ->join('nivels as n', 'n.id', '=', 'hf.nivel_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            // Orden descendente por el primer token numérico de n.name (5to, 4to, 3ro, etc.)
            ->orderByRaw("CAST(SUBSTRING_INDEX(n.name, ' ', 1) AS UNSIGNED) DESC")
            ->orderBy('s.name')
            ->get([
                'hfs.id as hfs_id',
                'n.name as floor_name',      // "5to Piso"
                's.name  as service_name',   // "Cirugía Mujeres"
                's.abbreviation as abbr',    // "CM"
                's.category as category',    // "Medicina Interna"
            ]);

        $selectedIds = $assignedRows->pluck('hfs_id')->all();

        // --- DISPONIBLES (del hospital seleccionado), excluyendo los ya asignados a cualquier carrito ---
        $availableRows = HospitalFloorService::query()
            ->from('hospital_floor_services as hfs')
            ->join('hospital_floors as hf', 'hf.id', '=', 'hfs.hospital_floor_id')
            ->where('hf.hospital_id', $selectedHospital->id)
            ->join('nivels as n', 'n.id', '=', 'hf.nivel_id')
            ->join('services as s', 's.id', '=', 'hfs.service_id')
            ->leftJoin('cart_service as cs_any', 'cs_any.hospital_floor_service_id', '=', 'hfs.id')
            ->whereNull('cs_any.id') // excluye HFS ya asignados a cualquier carrito (cumple UNIQUE)
            ->orderByRaw("CAST(SUBSTRING_INDEX(n.name, ' ', 1) AS UNSIGNED) DESC")
            ->orderBy('s.name')
            ->get([
                'hfs.id as hfs_id',
                'n.name as floor_name',
                's.name  as service_name',
                's.abbreviation as abbr',
                's.category as category',
            ]);

        // Helper: "5to - {abbr} - {service_name} {category}"
        $makeText = function ($floorName, $abbr, $category, $serviceName) {
            // primer token del nivel ("5to" de "5to Piso")
            $level = trim(strtok($floorName, ' ')) ?: $floorName;

            $abbrTxt = $abbr ?: $serviceName; // si no hay abreviatura, usa nombre del servicio
            $catTxt  = $category ?: '';       // si no hay categoría, queda vacío

            // "5to - CM - Cirugía Mujeres Medicina Interna"  (sin paréntesis)
            $tail = trim($serviceName . ' ' . $catTxt);

            return sprintf('%s - %s - %s', $level, $abbrTxt, $tail);
        };

        // Mapear a objetos para la vista
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

    /**
     * Actualiza la ruta del carrito (reemplaza las asignaciones por el orden enviado).
     * Usa SOLO columnas existentes en cart_service (según tu migración).
     */
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

        // Validación: todos los services[] deben pertenecer al hospital elegido
        if (!empty($services)) {
            $countBelong = DB::table('hospital_floor_services as hfs')
                ->join('hospital_floors as hf', 'hf.id', '=', 'hfs.hospital_floor_id')
                ->where('hf.hospital_id', $hospitalId)
                ->whereIn('hfs.id', $services)
                ->count();

            if ($countBelong !== count($services)) {
                return back()->withErrors('Hay servicios que no pertenecen al hospital seleccionado.')->withInput();
            }

            // Validación de unicidad global: ninguno debe estar ya asignado a otro carrito
            $conflicts = DB::table('cart_service')
                ->whereIn('hospital_floor_service_id', $services)
                ->where('cart_id', '!=', $cartId)
                ->exists();

            if ($conflicts) {
                return back()->withErrors('Algunos servicios ya están asignados a otro carrito.')->withInput();
            }
        }

        DB::transaction(function () use ($cartId, $services) {
            // Borrar asignaciones previas del carrito
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
