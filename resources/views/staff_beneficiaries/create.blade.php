@extends('partials.layouts.master2')

@section('title', 'Nuevo beneficiario')
@section('sub-title', 'Personal autorizado')
@section('pagetitle', 'SIGENUH')

@section('content')
<div class="container-fluid max-w-6xl mx-auto">

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
            <strong>Registrar beneficiario</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('staff-beneficiaries.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Puesto</label>
                        <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-primary">Guardar</button>
                    <a href="{{ route('staff-beneficiaries.index') }}" class="btn btn-danger">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
