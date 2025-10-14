<?php

namespace App\Http\Controllers;

use App\Models\StaffBeneficiary;
use Illuminate\Http\Request;
use App\Models\Hospital;
use Illuminate\Support\Str;

class StaffBeneficiaryController extends Controller
{
    public function index(Request $request)
    {
        $showInactive = (bool) $request->boolean('show_inactive', false);

        $user = $request->user();
        $hospitalId = $user->hospital_selected ?? null;

        $beneficiaries = \App\Models\StaffBeneficiary::with('hospital:id,name')
            ->when($hospitalId, function ($q) use ($hospitalId) {
                $q->where(function ($qq) use ($hospitalId) {
                    $qq->where('hospital_id', $hospitalId)
                    ->orWhereNull('hospital_id'); 
                });
            })
            ->when(!$showInactive, fn($q) => $q->where('status', 1))
            ->orderBy('full_name')
            ->get();

        return view('staff_beneficiaries.index', compact('beneficiaries', 'showInactive'));
    }

    public function datatable(Request $request)
    {
        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = trim((string) data_get($request->input('search', []), 'value', ''));
        $orderColIdx = (int) data_get($request->input('order', [0 => ['column' => 0]]), '0.column', 0);
        $orderDir    = data_get($request->input('order', [0 => ['dir' => 'asc']]), '0.dir', 'asc');

        $columns = ['full_name', 'job_title', 'status', 'created_at'];
        $orderBy = $columns[$orderColIdx] ?? 'full_name';
        $orderDir = strtolower($orderDir) === 'desc' ? 'desc' : 'asc';

        $user       = $request->user();
        $hospitalId = method_exists($user, 'resolveHospitalId')
            ? $user->resolveHospitalId()
            : ($user->hospital_selected ?? null);

        $base = StaffBeneficiary::query()
            ->when($hospitalId, fn($q) => $q->where('hospital_id', $hospitalId));

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $like = '%' . str_replace(['%','_'], ['\%','\_'], $search) . '%';
            $base->where(function ($q) use ($like) {
                $q
                ->where('full_name', 'like', $like)
                ->orWhere('job_title', 'like', $like);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (StaffBeneficiary $b) {
            $estadoBadge = $b->status
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-secondary">Inactivo</span>';

            $acciones = view('staff_beneficiaries.partials.actions', ['b' => $b])->render();

            return [
                e($b->full_name),
                e($b->job_title),
                $estadoBadge,
                e(optional($b->created_at)->format('Y-m-d H:i')),
                $acciones,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function create()
    {
        $beneficiary = new StaffBeneficiary();
        return view('staff_beneficiaries.create', compact('beneficiary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required','string','max:150'],
            'job_title' => ['nullable','string','max:120'],
        ]);

        $user       = $request->user();
        $hospitalId = $user->hospital_selected ?? null;

        // ¿Existe activo con mismo nombre y hospital?
        $existsActive = StaffBeneficiary::where([
            ['full_name', '=', $data['full_name']],
            ['hospital_id', '=', $hospitalId],
            ['status', '=', 1],
        ])->exists();

        if ($existsActive) {
            return back()->withErrors([
                'full_name' => 'Ya existe un beneficiario activo con este nombre en este hospital.'
            ])->withInput();
        }
        
        StaffBeneficiary::create([
            'hospital_id' => $hospitalId, 
            'full_name'   => $data['full_name'],
            'job_title'   => $data['job_title'] ?? null,
            'status'      => true,
        ]);

        return redirect()
            ->route('staff-beneficiaries.index')
            ->with('success', 'Beneficiario creado.');
    }

    public function show(StaffBeneficiary $staff_beneficiary)
    {
        return view('staff_beneficiaries.show', ['beneficiary' => $staff_beneficiary]);
    }

    public function toggleStatus(Request $request, \App\Models\StaffBeneficiary $staff_beneficiary)
    {
        $staff_beneficiary->update(['status' => ! $staff_beneficiary->status]);
        return back()->with('success', $staff_beneficiary->status ? 'Beneficiario activado.' : 'Beneficiario desactivado.');
    }


    public function edit(\App\Models\StaffBeneficiary $staff_beneficiary)
    {
        $hospitals = Hospital::orderBy('name')->get(['id','name']);

        return view('staff_beneficiaries.edit', [
            'beneficiary' => $staff_beneficiary,
            'hospitals'   => $hospitals,
        ]);
    }

    public function update(Request $request, \App\Models\StaffBeneficiary $staff_beneficiary)
    {
        $data = $request->validate([
            'full_name'   => ['required','string','max:150'],
            'job_title'   => ['nullable','string','max:120'],
            'hospital_id' => ['nullable','exists:hospitals,id'],
            'status'      => ['required','boolean'],
        ]);

        $staff_beneficiary->update($data);

        return redirect()
            ->route('staff-beneficiaries.index')
            ->with('success', 'Beneficiario actualizado.');
    }

    public function destroy(StaffBeneficiary $staff_beneficiary)
    {
        $staff_beneficiary->delete();
        return redirect()->route('staff-beneficiaries.index')->with('success', 'Beneficiario eliminado.');
    }
}
