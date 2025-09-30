@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Hospitales -> Beneficiarios -> Editar')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')

<div class="row g-4">
    <div class="col-12">
        <div class="card mb-0 h-100">

            <div class="card-header d-flex align-items-center gap-2">
                <strong>Editar beneficiario</strong>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('staff-beneficiaries.update', $beneficiary) }}">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        {{-- Nombre completo --}}
                        <div class="col-md-8">
                            <label class="form-label">Nombre completo</label>
                            <input type="text"
                                   name="full_name"
                                   class="form-control"
                                   value="{{ old('full_name', $beneficiary->full_name) }}"
                                   required>
                        </div>

                        {{-- Puesto --}}
                        <div class="col-md-4">
                            <label class="form-label">Puesto</label>
                            <input type="text"
                                   name="job_title"
                                   class="form-control"
                                   value="{{ old('job_title', $beneficiary->job_title) }}">
                        </div>

                        {{-- Hospital --}}
                        <div class="col-md-6">
                            <label class="form-label">Hospital</label>
                            <select name="hospital_id" class="form-select">
                                <option value="">— Seleccionar hospital —</option>
                                @foreach($hospitals as $h)
                                    <option value="{{ $h->id }}"
                                        @selected(old('hospital_id', $beneficiary->hospital_id) == $h->id)>
                                        {{ $h->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="1" @selected(old('status', $beneficiary->status)==1)>Activo</option>
                                <option value="0" @selected(old('status', $beneficiary->status)==0)>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary">
                            Actualizar
                        </button>
                        <a href="{{ route('staff-beneficiaries.index') }}" class="btn btn-danger">Cancelar</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@include('partials.social-share-modal')

@endsection

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
