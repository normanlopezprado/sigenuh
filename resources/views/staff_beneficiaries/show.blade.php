@extends('partials.layouts.master2')

@section('title', 'Detalle beneficiario')
@section('sub-title', 'Personal autorizado')
@section('pagetitle', 'SIGENUH')

@section('content')
<div class="container-fluid max-w-6xl mx-auto">
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="ri-user-3-line"></i> <strong>Detalle</strong>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Nombre</dt>
                <dd class="col-sm-9">{{ $beneficiary->full_name }}</dd>

                <dt class="col-sm-3">Puesto</dt>
                <dd class="col-sm-9">{{ $beneficiary->job_title }}</dd>

                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">
                    <span class="badge {{ $beneficiary->status ? 'bg-success' : 'bg-secondary' }}">
                    </span>
                </dd>

                <dt class="col-sm-3">Creado</dt>
                <dd class="col-sm-9">{{ optional($beneficiary->created_at)->format('Y-m-d H:i') }}</dd>

                <dt class="col-sm-3">Actualizado</dt>
                <dd class="col-sm-9">{{ optional($beneficiary->updated_at)->format('Y-m-d H:i') }}</dd>
            </dl>

            <div class="d-flex gap-2">
                <a href="{{ route('staff-beneficiaries.index') }}" class="btn btn-light">Volver</a>
                <a href="{{ route('staff-beneficiaries.edit', $beneficiary) }}" class="btn btn-warning">
                    <i class="ri-edit-2-line"></i> Editar
                </a>
                <form action="{{ route('staff-beneficiaries.destroy', $beneficiary) }}" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger"><i class="ri-delete-bin-6-line"></i> Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
