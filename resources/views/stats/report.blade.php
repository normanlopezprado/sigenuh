{{-- resources/views/stats/report.blade.php --}}
@extends('partials.layouts.master')

@section('title', 'SIGENUH')
@section('sub-title', 'Reportes')
@section('pagetitle', 'Estadísticas y reportes')


@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* ====== GRID UTIL ====== */
        .kpi-grid, .charts-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        }
        .kpi-col, .chart-col { grid-column: span 12; }
        @media (min-width: 768px) {
        .kpi-col { grid-column: span 6; }
        }
        /* Antes span 3 (4 por fila). Ahora 5 por fila en ≥1200px */
        @media (min-width: 1200px) {
        .kpi-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .kpi-col { grid-column: span 1; }
        }

        .mini-hint {
        font-size: .825rem;
        color: var(--bs-secondary-color, #6c757d);
        }

        /* Reducimos tamaño y padding SOLO en KPIs para que quepan */
        .kpi-grid .card .card-body { padding: 12px; }

        .stat-value {
        /* antes: clamp(1.35rem, 2.2vw, 2rem) */
        font-size: clamp(1.1rem, 1.4vw, 1.6rem);
        font-weight: 700;
        line-height: 1.1;
        }
        .stat-label {
        /* antes: .9rem */
        font-size: .8rem;
        color: var(--bs-secondary-color, #6c757d);
        }
        /* ====== CHARTS LAYOUT ====== */
        /* 1 por fila en móvil */
        .charts-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        }
        .chart-col { grid-column: span 12; }

        /* 2 por fila desde ≥768px (tablet y desktop) */
        @media (min-width: 768px) {
        .chart-col { grid-column: span 6; } /* 12/6 = 2 por fila */
        }

        .range-presets .btn { padding: 4px 10px; line-height: 1; }
        .range-presets .btn.active { outline: 2px solid var(--bs-primary); }
        .flatpickr-calendar { z-index: 1060; }
         /* por encima de toolbars, etc. */


        /* Altura responsiva; el gráfico llena el 100% del contenedor */
        .chart-body{
        height: clamp(260px, 38vh, 460px);
        position: relative;
        }
        .chart-body > #chartBySex,
        .chart-body > canvas {
        width: 100% !important;
        height: 100% !important;
        display: block;
        }


    </style>
    @endsection


    @section('content')
    {{-- === ENCABEZADO CONTEXTO === --}}
    

    {{-- === FILTROS (con Flatpickr embebido) === --}}
    <div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div>
        <h5 class="mb-0">Historia</h5>
        <div class="mini-hint">
            @php $today = ($today ?? \Carbon\Carbon::today()); @endphp
            <span>{{ $today->translatedFormat('l d \\de F, Y') }}</span>
        </div>
        </div>

        <div class="ms-auto text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExportXlsx" disabled>Exportar Excel</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExportPdf" disabled>Exportar PDF</button>
        </div>
        <div class="mini-hint mt-1">Se habilitarán cuando definamos los endpoints.</div>
        </div>
    </div>

    <div class="card-body">
        <form id="filtersForm" method="GET" action="">
        @csrf

        <div class="row g-3 align-items-end mb-2">
            <div class="col-12">
            <label for="dateRange" class="form-label">Rango de fechas</label>
            <div class="input-group">
                <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                <input
                type="text"
                id="dateRange"
                name="date_range"
                class="form-control"
                placeholder="YYYY-MM-DD - YYYY-MM-DD"
                value="{{ request('date_range') }}"
                autocomplete="off">
                <button class="btn btn-light" type="button" id="btnClearRange" title="Limpiar">
                <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="range-presets mt-2 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="today">Hoy</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="yesterday">Ayer</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="last7">Últimos 7 días</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="last30">Últimos 30 días</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="thisMonth">Este mes</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="lastMonth">Mes pasado</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-range="thisYear">Año actual</button>
            </div>

            <div class="mini-hint mt-1">El rango se aplica a <strong>todas</strong> las métricas y gráficas.</div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Aplicar</button>
            <a href="{{ url()->current() }}" class="btn btn-light">Limpiar</a>
            </div>
        </div>
        </form>
    </div>
    </div>

    {{-- ===== JS embebido: carga Flatpickr y lo inicializa aquí mismo ===== --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
        

    <script>
    (function() {
    'use strict';

    // Si por alguna razón tu layout tenía otra versión en cache, forzamos esta
    if (!window.flatpickr) {
        console.error('[SIGENUH] Flatpickr no se cargó.');
        return;
    }

    const $input   = document.getElementById('dateRange');
    const $clear   = document.getElementById('btnClearRange');
    const $presets = document.querySelectorAll('.range-presets [data-range]');
    const form     = document.getElementById('filtersForm');
    if (!$input) return;

    const pad = n => String(n).padStart(2,'0');
    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    const today = () => { const t = new Date(); return new Date(t.getFullYear(), t.getMonth(), t.getDate()); };
    const firstDayOfMonth = (y,m) => new Date(y, m, 1);
    const lastDayOfMonth  = (y,m) => new Date(y, m+1, 0);
    const parsePair = (v) => (v && v.includes(' - ')) ? v.split(' - ').map(s => s.trim()) : [];

    const es = Object.assign({}, window.flatpickr.l10ns.es || {});
    es.rangeSeparator = ' - ';

    const mq = window.matchMedia('(min-width: 768px)');
    const fp = window.flatpickr($input, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        allowInput: true,
        disableMobile: true,
        position: 'auto',
        showMonths: mq.matches ? 2 : 1,
        locale: es,
        defaultDate: parsePair($input.value),
        onOpen: () => fp.set('showMonths', mq.matches ? 2 : 1),
        onClose: (selectedDates) => {
        if (selectedDates && selectedDates.length === 2) {
            $input.value = `${fmt(selectedDates[0])} - ${fmt(selectedDates[1])}`;
            if (form) form.submit(); // filtra KPIs + charts
        }
        }
    });

    // Abrir calendario al foco/clic (por si hay wrappers)
    $input.addEventListener('focus', () => fp.open());
    $input.addEventListener('click',  () => fp.open());

    // Adaptar 1/2 meses según ancho
    const onMQ = (e) => fp.set('showMonths', e.matches ? 2 : 1);
    if (mq.addEventListener) mq.addEventListener('change', onMQ);
    else mq.addListener(onMQ);

    // Presets
    const setRange = (start, end) => {
        fp.setDate([fmt(start), fmt(end)], true, 'Y-m-d');
        if (form) form.submit();
    };

    const presets = {
        today()     { const d = today(); setRange(d,d); },
        yesterday() { const d = today(); d.setDate(d.getDate()-1); setRange(d,d); },
        last7()     { const end = today(); const start = new Date(end); start.setDate(start.getDate()-6); setRange(start,end); },
        last30()    { const end = today(); const start = new Date(end); start.setDate(start.getDate()-29); setRange(start,end); },
        thisMonth() { const t = today(); setRange(firstDayOfMonth(t.getFullYear(),t.getMonth()), lastDayOfMonth(t.getFullYear(),t.getMonth())); },
        lastMonth() { const t = today(); const m = t.getMonth()-1; const y = m<0?t.getFullYear()-1:t.getFullYear(); const mm=(m+12)%12;
                    setRange(firstDayOfMonth(y,mm), lastDayOfMonth(y,mm)); },
        thisYear()  { const t = today(); setRange(new Date(t.getFullYear(),0,1), new Date(t.getFullYear(),11,31)); }
    };

    $presets.forEach(btn => {
        btn.addEventListener('click', () => {
        $presets.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const key = btn.getAttribute('data-range');
        if (presets[key]) presets[key]();
        });
    });

    // Limpiar
    if ($clear) {
        $clear.addEventListener('click', () => {
        fp.clear();
        $presets.forEach(b => b.classList.remove('active'));
        // Si quieres, también submit aquí
        // if (form) form.submit();
        });
    }
    })();
    </script>

    <style>
    /* Asegura visibilidad por encima de toolbars/modales */
    .flatpickr-calendar { z-index: 1060; }
    </style>


    {{-- === KPIs === --}}
    <div class="kpi-grid mb-3">
        {{-- Pacientes atendidos --}}
        <div class="kpi-col">
        <div class="card h-100">
            <div class="card-body">
            <div class="stat-value">{{ $kpis['pacientes'] ?? 0 }}</div>
            <div class="stat-label">Pacientes atendidos</div>
            </div>
        </div>
        </div>
        {{-- Acompañanstes --}}
        <div class="kpi-col">
        <div class="card h-100">
            <div class="card-body">
                <div class="stat-value">{{ $kpis['acompanantes'] ?? 0 }}</div>
            <div class="stat-label">Acompañantes atendidos</div>
            </div>
        </div>
        </div>
        {{-- Colaboradores atendidos --}}
        <div class="kpi-col">
        <div class="card h-100">
            <div class="card-body">
            <div class="stat-value">{{ $kpis['colaboradores'] ?? 0 }}</div>
            <div class="stat-label">Colaboradores atendidos</div>
            </div>
        </div>
        </div>
        {{-- Bandejas --}}
        <div class="kpi-col">
        <div class="card h-100">
            <div class="card-body">
            <div class="stat-value">{{ $kpis['bandejas'] ?? 0 }}</div>
            <div class="stat-label">Bandejas entregadas</div>
            </div>
        </div>
        </div>
        {{-- desechables --}}
        <div class="kpi-col">
        <div class="card h-100">
            <div class="card-body">
            <div class="stat-value">{{ $kpis['desechables'] ?? 0 }}</div>
            <div class="stat-label">Descartables entregados</div>
            </div>
        </div>
        </div>
    </div>

    {{-- === CHARTS === --}}
    <div class="charts-grid mb-3">
        {{-- Distribución por sexo --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Géneros de pacientes</h6>
            </div>
            <div class="card-body chart-body">
            <div id="chartBySex"></div>
            <div class="mini-hint mt-2">Hombres / Mujeres / Niños.</div>
            </div>
        </div>
        </div>

        {{-- Dietas entregadas --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Dietas entregadas</h6>
            </div>
            <div class="card-body">
            <canvas id="chartByDiet" height="300"></canvas>


            </div>
        </div>
        </div>

        {{-- Bandejas por tiempo (placeholder) --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Bandejas por tiempo de comida</h6>
            </div>
            <div class="card-body">
            <div id="chartByMeal" style="min-height: 300px;" class="d-flex align-items-center justify-content-center text-muted">
                <span class="mini-hint">Próximamente: gráfico por tiempo (Desayuno/Almuerzo/Cena)</span>
            </div>
            </div>
        </div>
        </div>

        {{-- Top servicios con más pacientes atendidos --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Servicios con más pacientes atendidos</h6>
            </div>
            <div class="card-body">
            <canvas id="chartTopServices" style="min-height: 300px;"></canvas>
            <div class="mini-hint mt-2">Ordenado por pacientes (SUM de bandejas).</div>
            </div>
        </div>
        </div>
    </div>
    @endsection


@section('scripts')

    {{-- ================================================================
       FRONTEND SCRIPTS — STATS REPORT
       - Este bloque contiene TODO el JS de la vista.
       - Estructurado por secciones con encabezados visibles.
    ================================================================= --}}

    {{-- ----------------------------------------------------------------
       [DEPS] FLATPICKR (calendario de rango)
       - Carga de librería y locale ES.
    ----------------------------------------------------------------- --}}
    {{-- [DEPS] CHART.JS (para las gráficas) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        {{-- [BOOTSTRAP] JSON para Sex Chart (no anidar dentro de de JS) --}}
        {{-- [BOOTSTRAP] JSON para Sex Chart (no anidar dentro de JS) --}}
        <script id="sexPayloadJSON" type="application/json">
        {!! json_encode(
            $sexPayload ?? ['labels' => ['Hombres','Mujeres','Niños'], 'data' => [0,0,0]],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) !!}
        </script>
        
        


        <script>
        (function(){
        'use strict';
        let payload = { labels: ['Hombres','Mujeres','Niños'], data: [0,0,0] };
        const src = document.getElementById('sexPayloadJSON');
        if (src && src.textContent) {
            try { payload = JSON.parse(src.textContent); }
            catch (e) { console.error('[SIGENUH] No se pudo parsear sexPayloadJSON:', e); }
        }
        window.__SIGENUH__ = window.__SIGENUH__ || {};
        const NS = window.__SIGENUH__;
        NS.SERVER = Object.assign({}, NS.SERVER || {}, { sexPayload: payload });
        console.log('[SIGENUH] sexPayload listo:', payload);
        })();

        </script>




    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    {{-- ----------------------------------------------------------------
       [BLOQUE 1] DATE RANGE PICKER (FLATPICKR)
       - Visual, con 2 meses en desktop, presets y auto-submit.
       - El rango se envía como: "YYYY-MM-DD - YYYY-MM-DD".
       - Afecta a TODOS los KPIs y Charts del reporte.
    ----------------------------------------------------------------- --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

        'use strict';

        // --- [1.0] Verificación de dependencia ----------------------------------
        if (!window.flatpickr) {
            console.warn('[SIGENUH] Flatpickr no está cargado.');
            return;
        }

        // --- [1.1] Referencias al DOM -------------------------------------------
        const $input   = document.getElementById('dateRange');
        const $clear   = document.getElementById('btnClearRange');
        const $presets = document.querySelectorAll('.range-presets [data-range]');
        const form     = document.getElementById('filtersForm');

        if (!$input) return;

        // --- [1.2] Helpers de fecha ---------------------------------------------
        const pad = n => String(n).padStart(2, '0');
        const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        const today = () => { const t = new Date(); return new Date(t.getFullYear(), t.getMonth(), t.getDate()); };
        const firstDayOfMonth = (y,m) => new Date(y, m, 1);
        const lastDayOfMonth  = (y,m) => new Date(y, m+1, 0);

        // Lee valor actual: "YYYY-MM-DD - YYYY-MM-DD"
        const parsePair = (v) => {
            if (!v || !v.includes(' - ')) return [];
            const [a,b] = v.split(' - ').map(s => s.trim());
            return [a,b];
        };

        // --- [1.3] Configuración de locale ES y separador de rango --------------
        const es = Object.assign({}, flatpickr.l10ns.es || {});
        es.rangeSeparator = ' - ';

        // --- [1.4] Inicialización Flatpickr (1 o 2 meses según ancho) -----------
        const mq = window.matchMedia('(min-width: 768px)');
        const fp = flatpickr($input, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            allowInput: true,
            disableMobile: true,
            position: 'auto',
            showMonths: mq.matches ? 2 : 1,
            locale: es,
            defaultDate: parsePair($input.value),
            onOpen: () => fp.set('showMonths', mq.matches ? 2 : 1),

            // Auto-submit cuando hay dos fechas seleccionadas
            onClose: (selectedDates) => {
            if (selectedDates && selectedDates.length === 2) {
                $input.value = `${fmt(selectedDates[0])} - ${fmt(selectedDates[1])}`;
                if (form) form.submit(); // <-- recalcula KPIs + charts en backend
            }
            }
        });

            


        // --- [1.5] UX: abrir calendario al enfocar/click ------------------------
        $input.addEventListener('focus', () => fp.open());
        $input.addEventListener('click',  () => fp.open());

        // --- [1.6] Responsivo: 1/2 meses según ancho ----------------------------
        const onMQ = (e) => fp.set('showMonths', e.matches ? 2 : 1);
        if (mq.addEventListener) mq.addEventListener('change', onMQ);
        else mq.addListener(onMQ);

        // --- [1.7] Presets / Atajos ---------------------------------------------
        const setRange = (start, end) => {
            fp.setDate([fmt(start), fmt(end)], true, 'Y-m-d');
            if (form) form.submit(); // <-- aplica rango inmediatamente
        };

        const presets = {
            today()     { const d = today(); setRange(d,d); },
            yesterday() { const d = today(); d.setDate(d.getDate() - 1); setRange(d,d); },
            last7()     { const end = today(); const start = new Date(end); start.setDate(start.getDate() - 6); setRange(start,end); },
            last30()    { const end = today(); const start = new Date(end); start.setDate(start.getDate() - 29); setRange(start,end); },
            thisMonth() { const t = today(); setRange(firstDayOfMonth(t.getFullYear(), t.getMonth()), lastDayOfMonth(t.getFullYear(), t.getMonth())); },
            lastMonth() {
            const t = today();
            const m = t.getMonth() - 1;
            const y = m < 0 ? t.getFullYear() - 1 : t.getFullYear();
            const mm = (m + 12) % 12;
            setRange(firstDayOfMonth(y, mm), lastDayOfMonth(y, mm));
            },
            thisYear()  { const t = today(); setRange(new Date(t.getFullYear(), 0, 1), new Date(t.getFullYear(), 11, 31)); }
        };

        $presets.forEach(btn => {
            btn.addEventListener('click', () => {
            $presets.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const key = btn.getAttribute('data-range');
            if (presets[key]) presets[key]();
            });
        });

        // --- [1.8] Botón limpiar -------------------------------------------------
        if ($clear) {
            $clear.addEventListener('click', () => {
            fp.clear();
            $presets.forEach(b => b.classList.remove('active'));
            // Si quieres enviar limpio automáticamente, descomenta:
            // if (form) form.submit();
            });
        }
        });

        
    </script>

    {{-- [BLOQUE 2.1] DISTRIBUCIÓN POR SEXO (PIE) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const el = document.getElementById('chartBySex');
    if (!el) return;
    if (!window.ApexCharts) {
        console.error('[SIGENUH] Falta ApexCharts');
        return;
    }

    let payload = { labels: ['Hombres','Mujeres','Niños'], data: [0,0,0] };
    const src = document.getElementById('sexPayloadJSON');
    if (src && src.textContent) {
        try { payload = JSON.parse(src.textContent); }
        catch(e) { console.error('[SIGENUH] No se pudo parsear sexPayloadJSON:', e); }
    }

    const total  = Array.isArray(payload.data) ? payload.data.reduce((a,b)=>a+b,0) : 0;
    const series = total > 0 ? payload.data   : [1];
    const labels = total > 0 ? payload.labels : ['Sin datos'];

    if (window.__sexApex) { window.__sexApex.destroy(); }

    const options = {
        chart: { type: 'polarArea', height: '100%' },
        series: series,
        labels: labels,
        stroke: { width: 1 },
        legend: { position: 'bottom' },
        dataLabels: {
        enabled: true,
        formatter: function (val, opts) {
            const v = opts.series[opts.seriesIndex] || 0;
            if (total > 0) {
            const pct = (v * 100 / total).toFixed(1);
            return `${v} • ${pct}%`;
            }
            return `${v}`;
        }
        },
        tooltip: {
        y: {
            formatter: function (v) {
            if (total > 0) {
                const pct = (v * 100 / total).toFixed(1);
                return `${v} (${pct}%)`;
            }
            return String(v);
            }
        }
        },
        noData: { text: 'Sin datos' },
        responsive: [{ breakpoint: 768, options: { legend: { position: 'bottom' } } }]
    };

    window.__sexApex = new ApexCharts(el, options);
    window.__sexApex.render();
    });
    </script>



@endsection

