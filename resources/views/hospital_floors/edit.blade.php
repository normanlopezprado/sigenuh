@extends('partials.layouts.master')

@section('title', 'SIGENUH')

@section('sub-title', 'Hospitales -> Asignar plantas a hospital ')
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
                    <h5 class="card-title mb-0">Asignar plantas a hospital</h5>
                </div>
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
                        <form method="POST" action="{{ route('hospital-floors.update') }}">
                            @csrf
                            <label class="form-check">
                                <input type="checkbox" id="checkAll" class="form-check-input">
                                <span class="form-check-label">Seleccionar todos</span>
                            </label>
                            <hr>
                            @foreach($niveles as $n)
                                <div class="col-md-12 form-label">
                                    <label class="form-check-label">
                                    <input
                                        type="checkbox"
                                        name="niveles[]"
                                        class="form-check-input nivel-item"
                                        value="{{ $n->id }}"
                                        @checked(in_array($n->id, old('niveles', $seleccionados)))
                                    >

                                        <span>
                                            <strong>{{ $n->name }}</strong>
                                               @if(!empty($n->description))
                                                <br><small class="">{{ $n->description }}</small>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                            @push('scripts')
                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        const checkAll = document.getElementById('checkAll');
                                        const items = document.querySelectorAll('.nivel-item');

                                        checkAll?.addEventListener('change', (e) => {
                                            items.forEach(el => el.checked = e.target.checked);
                                        });
                                    });
                                </script>
                            @endpush
                            @can('hospitalfloors.update')
                            <div class="mt-3">
                                <button class="btn btn-primary">Guardar asignación</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-danger">Cancelar</a>
                            </div>
                            @endcan
                        </form>
                    </div>
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


