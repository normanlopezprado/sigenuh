@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Servicios -> Asignar servicios a plantas')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@yaireo/tagify/tagify.css') }}">
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Asignar servicios a plantas</h5>
                </div>
                @can('hospital-floor-services.edit')
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success mt-2">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="col-md-12 form-floating form-label">

                        <form method="GET" action="{{ route('hospital-floor-services.edit') }}" class="row g-2 mb-3">
                            <div class="col-md-8">
                                <select name="floor" class="form-select" onchange="this.form.submit()">
                                    @foreach($floors as $f)
                                        <option value="{{ $f->id }}" @selected(optional($selectedFloor)->id === $f->id)>
                                            {{ $f->nivel?->name ?? 'Piso' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-secondary w-100">Cambiar piso</button>
                            </div>
                        </form>

                        <hr>
                        @if($selectedFloor)
                            <form method="POST" action="{{ route('hospital-floor-services.update') }}">
                                @csrf
                                <input type="hidden" name="floor" value="{{ $selectedFloor->id }}">
                                <div class="mb-2">
                                    <label class="form-check">
                                        <input type="checkbox" id="checkAll" class="form-check-input">
                                        <span class="form-check-label">Seleccionar todos</span>
                                    </label>
                                </div>
                                <hr>

                                <div class="col-md-12 form-floating form-label">
                                    @foreach($services as $s)
                                        <div class="col-md-12 form-label">
                                            <label class="form-check-label">
                                                <input type="checkbox"
                                                       class="form-check-input service-item"
                                                       name="services[]"
                                                       value="{{ $s->id }}"
                                                    @checked(in_array($s->id, old('services', $selectedServiceIds)))>
                                                <span><strong>{{ $s->name }}</strong>
                                                @if(!empty($s->abbreviation)) <small class="text-muted ms-1">({{ $s->abbreviation }})</small> @endif
                                              </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @can('hospital-floor-services.update')
                                <div class="mt-3">
                                    <button class="btn btn-primary">Guardar</button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                                </div>
                                @endcan
                            </form>
                        @endif

                        @push('scripts')
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const all = document.getElementById('checkAll');
                                    const items = document.querySelectorAll('.service-item');
                                    all?.addEventListener('change', e => items.forEach(el => el.checked = e.target.checked));
                                });
                            </script>
                        @endpush
                    </div>
                    @endcan

                </div>
            </div>
        </div>
    </div>

    @include('partials.social-share-modal')

@endsection

@section('js')

    <!-- Datatable js -->
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <!-- Datatable init -->
    <script src="{{ asset('assets/js/table/datatable.init.js') }}"></script>
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

@endsection


