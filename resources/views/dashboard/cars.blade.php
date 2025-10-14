@extends('partials.layouts.master3')

@section('title', 'Sistema de gestión nutricional Hospitalaria')
@section('sub-title', 'Dashboard')
@section('pagetitle', 'Carritos')
@section('buttonTitle', 'Share')
@section('link', '#!')

@section('css')
<style>
    .cart-color-dot { width:.85rem; height:.85rem; border-radius:50%; display:inline-block; margin-right:.35rem; border:1px solid rgba(0,0,0,.1); }
    .card-hover:hover { box-shadow: 0 0.25rem 1rem rgba(0,0,0,.08); }
    .header-meta { line-height:1.15; }
</style>
@endsection

@section('content')
@php
    use Carbon\Carbon;
    $_fecha = now()->toDateString();
    $c = Carbon::parse($_fecha)->locale('es');
    $diaLargo = ucfirst($c->isoFormat('dddd'));
    $fechaLarga = $c->isoFormat('D [de] MMMM');
    $horaInicial = now()->format('H:i:s');
@endphp

<input type="hidden" id="activeDateInput" value="{{ $_fecha }}">

<div class="row g-4">
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header d-flex flex-wrap gap-3 align-items-center">
                <div class="header-meta">
                    <h5 class="card-title mb-1">
                        Ventana de recolección - ({{ $activeMeal ?? 'Desayuno' }}) <span class="text-success">Abierta</span>
                    </h5>
                    <div class="text-muted small">
                        Día {{ $diaLargo }} - {{ $fechaLarga }} | Hora de actualización: <span id="horaActualizacion">{{ $horaInicial }}</span>
                    </div>
                </div>

                <div class="ms-auto d-flex align-items-center gap-2">
                    <label class="mb-0 small text-muted">Hospital</label>
                    <select id="hospitalSelect" class="form-select form-select-sm" style="min-width: 250px;">
                        @foreach($hospitals as $h)
                            <option value="{{ $h->id }}" {{ $selectedHospitalId == $h->id ? 'selected' : '' }}>
                                {{ $h->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-muted small">Auto-refresco: 5s</span>
                </div>
            </div>

            <div class="card-body">
                <div id="cards-container">
                    @include('partials.dashboard.carts-cards', [
                        'carts'         => $carts,
                        'dietTypes'     => $dietTypes,
                        'cartDietStats' => $cartDietStats,
                        'activeDate'    => $_fecha,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
    const ENDPOINT = @json(route('dashboard.cars.partial'));
    const REFRESH_MS = 5000;
    const hospitalSelect = document.getElementById('hospitalSelect');
    const container = document.getElementById('cards-container');
    const horaEl = document.getElementById('horaActualizacion');
    const dateInput = document.getElementById('activeDateInput');

    setInterval(() => {
        const now = new Date();
        horaEl.textContent = now.toLocaleTimeString('es-GT',{hour12:false});
    }, 1000);

    async function refreshCards(){
        try {
            const res = await fetch(`${ENDPOINT}?hospital_id=${hospitalSelect.value}&date=${dateInput.value}`, {
                headers: {'X-Requested-With':'XMLHttpRequest'}
            });
            const data = await res.json();
            if(data.ok) container.innerHTML = data.html;
        } catch(e){}
    }

    hospitalSelect.addEventListener('change', refreshCards);
    setInterval(refreshCards, REFRESH_MS);
})();
</script>
@endsection
