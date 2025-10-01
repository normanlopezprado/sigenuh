<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartService;
use App\Models\HospitalFloorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /* ========================
     * CRUD de Carts (Carritos)
     * ======================== */

   public function index(Request $request)
    {
        $hospitalId   = $request->user()->hospital_selected;
        $showInactive = (bool) $request->get('show_inactive', false);

        $carts = \App\Models\Cart::forHospital($hospitalId)
            ->when(!$showInactive, fn($q) => $q->where('status', true))
            ->ordered()
            ->get();

        return view('carts.index', compact('carts', 'showInactive'));
    }

    public function create(Request $request)
    {
        return view('carts.create');
    }

    public function store(Request $request)
    {
        $hospitalId = $request->user()->hospital_selected;

        $data = $request->validate([
            'name'      => ['required','string','max:120',
                Rule::unique('carts')->where(fn($q)=>$q->where('hospital_id',$hospitalId))
            ],
            'code_name' => ['required','string','max:120',
                Rule::unique('carts')->where(fn($q)=>$q->where('hospital_id',$hospitalId))
            ],
            'color'     => ['nullable','string','max:20'],
            'order'     => ['nullable','integer','min:0'],
            'status'    => ['sometimes','boolean'],
            'notes'     => ['nullable','string'],
        ]);

        $data['hospital_id'] = $hospitalId;
        $data['status'] = $request->boolean('status', true);

        Cart::create($data);

        return redirect()->route('carts.index')->with('success', 'Carrito creado correctamente.');
    }

    public function show(Cart $cart)
    {
        // Vista simple de detalle (opcional)
        $cart->load(['hospital','services']);
        return view('carts.show', compact('cart'));
    }

    public function edit(Cart $cart)
    {
        return view('carts.edit', compact('cart'));
    }

    public function update(Request $request, Cart $cart)
    {
        $hospitalId = $request->user()->hospital_selected;

        $data = $request->validate([
            'name'      => ['required','string','max:120',
                Rule::unique('carts')->ignore($cart->id)->where(fn($q)=>$q->where('hospital_id',$hospitalId))
            ],
            'code_name' => ['required','string','max:120',
                Rule::unique('carts')->ignore($cart->id)->where(fn($q)=>$q->where('hospital_id',$hospitalId))
            ],
            'color'     => ['nullable','string','max:20'],
            'order'     => ['nullable','integer','min:0'],
            'status'    => ['sometimes','boolean'],
            'notes'     => ['nullable','string'],
        ]);

        $data['status'] = $request->boolean('status', true);

        $cart->update($data);

        return redirect()->route('carts.index')->with('success', 'Carrito actualizado correctamente.');
    }

    public function destroy(Cart $cart)
    {
        $cart->delete();
        return redirect()->route('carts.index')->with('success', 'Carrito eliminado.');
    }

    /* =======================================
     * Editor de Ruta (asignación de servicios)
     * ======================================= */

    // GET /carts/{cart}/route
    public function editRoute(Request $request, Cart $cart)
    {
        $hospitalId = $request->user()->hospital_selected;

        $assignedServiceIds = DB::table('cart_service')->pluck('hospital_floor_service_id')->all();

        $available = HospitalFloorService::with([
                'service:id,name,abbreviation',
                'hospitalFloor:id,name,nivel_id,hospital_id',
                'hospitalFloor.nivel:id,name', // no pedimos abbrev aquí
            ])
            ->whereHas('hospitalFloor', fn($q)=>$q->where('hospital_id',$hospitalId))
            ->whereNotIn('id', $assignedServiceIds)
            ->orderByDesc('created_at')
            ->get();

        $selected = HospitalFloorService::with([
                'service:id,name,abbreviation',
                'hospitalFloor:id,name,nivel_id,hospital_id',
                'hospitalFloor.nivel:id,name',
            ])
            ->whereHas('hospitalFloor', fn($q)=>$q->where('hospital_id',$hospitalId))
            ->whereHas('carts', fn($q)=>$q->where('cart_id',$cart->id))
            ->get();

        return view('carts.route', compact('cart','available','selected'));
    }


    // PUT /carts/{cart}/route
    public function updateRoute(Request $request, Cart $cart)
    {
        $validated = $request->validate([
            'selected'   => ['array'],
            'selected.*' => ['uuid','exists:hospital_floor_services,id'],
        ]);

        $ids = $validated['selected'] ?? [];

        try {
            DB::transaction(function () use ($ids, $cart) {
                // Borramos asignaciones actuales de este cart
                DB::table('cart_service')->where('cart_id', $cart->id)->delete();

                // Insertamos nuevas asignaciones respetando el orden
                $now = now();
                foreach ($ids as $index => $hfsId) {
                    DB::table('cart_service')->insert([
                        'id'                        => (string) Str::uuid(),
                        'cart_id'                   => $cart->id,
                        'hospital_floor_service_id' => $hfsId,
                        'order'                     => $index + 1,
                        'assigned_by'               => Auth::id(),
                        'assigned_at'               => $now,
                        'created_at'                => $now,
                        'updated_at'                => $now,
                    ]);
                }
            });
        } catch (QueryException $e) {
            // Esto atrapará violaciones de UNIQUE (exclusividad) si otra sesión asignó algo en paralelo
            return back()->with('error', 'No se pudo guardar la ruta: uno o más servicios ya fueron asignados a otro carrito. Actualiza la página e intenta de nuevo.');
        }

        return redirect()->route('carts.route.edit', $cart)->with('success', 'Ruta de reparto actualizada.');
    }

    /* ==========================================
     * Endpoints auxiliares (para UI con buscador)
     * ========================================== */

    // GET /carts/{cart}/services/available?q=...
    public function availableServices(Request $request, Cart $cart)
    {
        $hospitalId = $request->user()->hospital_selected;
        $q = trim((string) $request->get('q', ''));

        $assignedServiceIds = DB::table('cart_service')->pluck('hospital_floor_service_id')->all();

        $query = HospitalFloorService::with([
                'service:id,name,abbreviation',
                'hospitalFloor.nivel:id,name',
            ])
            ->whereHas('hospitalFloor', fn($qq)=>$qq->where('hospital_id',$hospitalId))
            ->whereNotIn('id', $assignedServiceIds);

        if ($q !== '') {
            $query->where(function($sub) use ($q){
                $sub->whereHas('service', function($s) use ($q){
                        $s->where('name','like',"%{$q}%")
                        ->orWhere('abbreviation','like',"%{$q}%");
                    })
                    ->orWhereHas('hospitalFloor.nivel', function($n) use ($q){
                        $n->where('name','like',"%{$q}%");
                    });
            });
        }

        $rows = $query->limit(50)->get()->map(function($r){
            return [
                'id'    => $r->id,
                'label' => sprintf('%s - %s (%s)',
                    $r->hospitalFloor->nivel->name ?? 'Nivel',
                    $r->service->name ?? 'Servicio',
                    $r->service->abbreviation ?? ''
                ),
            ];
        });

        return response()->json($rows);
    }


    // GET /carts/{cart}/services/selected
    public function selectedServices(Request $request, Cart $cart)
    {
        $hospitalId = $request->user()->hospital_selected;

        $rows = HospitalFloorService::with([
                'service:id,name,abbreviation',
                'hospitalFloor.nivel:id,name',
            ])
            ->whereHas('hospitalFloor', fn($q)=>$q->where('hospital_id',$hospitalId))
            ->whereHas('carts', fn($q)=>$q->where('cart_id',$cart->id))
            ->get()
            ->map(function($r){
                return [
                    'id'    => $r->id,
                    'label' => sprintf('%s - %s (%s)',
                        $r->hospitalFloor->nivel->name ?? 'Nivel',
                        $r->service->name ?? 'Servicio',
                        $r->service->abbreviation ?? ''
                    ),
                ];
            });

        return response()->json($rows);
    }

}
