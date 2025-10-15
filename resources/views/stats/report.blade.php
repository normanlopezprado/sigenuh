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
.kpi-grid, .charts-grid{
    display:grid; grid-template-columns:repeat(12,1fr); gap:12px;
}
.kpi-col, .chart-col{ grid-column:span 12; }
@media (min-width:768px){ .kpi-col{ grid-column:span 6; } .chart-col{ grid-column:span 6; } }
@media (min-width:1200px){
    .kpi-grid{ grid-template-columns:repeat(5, minmax(0,1fr)); }
    .kpi-col{ grid-column:span 1; }
}

.kpi-grid .card .card-body{ padding:12px; }
.stat-value{ font-size:clamp(1.1rem,1.4vw,1.6rem); font-weight:700; line-height:1.1; }
.stat-label{ font-size:.8rem; color: var(--bs-secondary-color,#6c757d); }
.mini-hint{ font-size:.825rem; color: var(--bs-secondary-color,#6c757d); }

/* Charts: el contenedor define la altura; el gráfico llena el 100% */
.chart-body{ height:clamp(260px,38vh,460px); position:relative; }
.chart-body > #chartBySex,
.chart-body > #chartByDiet,
.chart-body > canvas{ width:100% !important; height:100% !important; display:block; }

/* Flatpickr arriba de toolbars/modales */
.flatpickr-calendar{ z-index:1060; }


/* ====== CHARTS LAYOUT: 1 / 2 / 3 por fila ====== */
.charts-grid{
display: grid;
grid-template-columns: repeat(12, 1fr);
gap: 12px;
}

.chart-col{
grid-column: span 12;
}

@media (min-width: 768px){
.chart-col{ grid-column: span 6; } 
}

@media (min-width: 1200px){
.chart-col{ grid-column: span 4; } 
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

    {{-- === KPIs === --}}
    <div class="kpi-grid mb-3">
    <div class="kpi-col">
        <div class="card h-100"><div class="card-body">
        <div class="stat-value">{{ $kpis['pacientes'] ?? 0 }}</div>
        <div class="stat-label">Pacientes atendidos</div>
        </div></div>
    </div>

    <div class="kpi-col">
        <div class="card h-100"><div class="card-body">
        <div class="stat-value">{{ $kpis['acompanantes'] ?? 0 }}</div>
        <div class="stat-label">Acompañantes atendidos</div>
        </div></div>
    </div>

    <div class="kpi-col">
        <div class="card h-100"><div class="card-body">
        <div class="stat-value">{{ $kpis['colaboradores'] ?? 0 }}</div>
        <div class="stat-label">Colaboradores atendidos</div>
        </div></div>
    </div>

    <div class="kpi-col">
        <div class="card h-100"><div class="card-body">
        <div class="stat-value">{{ $kpis['bandejas'] ?? 0 }}</div>
        <div class="stat-label">Bandejas entregadas</div>
        </div></div>
    </div>

    <div class="kpi-col">
        <div class="card h-100"><div class="card-body">
        <div class="stat-value">{{ $kpis['desechables'] ?? 0 }}</div>
        <div class="stat-label">Descartables entregados</div>
        </div></div>
    </div>
    </div>

    {{-- === CHARTS === --}}
    <div class="charts-grid mb-3">

        {{-- Pirámide: servicios que más alimentan --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Servicios con más pascientes</h6>
            <div class="mini-hint mt-1">Mayor → menor </div>
            </div>
            <div class="card-body chart-body">
            <div id="chartServicesPyramid"></div>
            </div>
        </div>
        </div>

        {{-- Dietas entregadas --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Dietas entregadas</h6>
            <div class="mini-hint mt-1">Pacientes</div>
            </div>
            <div class="card-body chart-body">
            <div id="chartByDiet"></div>
            </div>
        </div>
        </div>

        {{-- Distribución por sexo --}}
        <div class="chart-col">
            <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">Géneros de pacientes</h6>
                <div class="mini-hint mt-2">Hombres / Mujeres / Niños.</div>
            </div>
            <div class="card-body chart-body">
                <div id="chartBySex"></div>
            </div>
            </div>
        </div>

        {{-- Comidas por servicio (Stacked Bar) --}}
        <div class="chart-col">
        <div class="card h-100">
            <div class="card-header">
            <h6 class="mb-0">Comidas por servicio</h6>
            <div class="mini-hint mt-1">Desayuno / Almuerzo / Cena • Horizontal</div>
            </div>
            <div class="card-body chart-body">
            <div id="chartMealsByService"></div>
            </div>
        </div>
        </div>

        


    </div>
    @endsection

// ------
// Js
// ------

@section('js')


{{-- Dependenciass: Flatpickr (calendario) y ApexCharts --}}

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>


{{-- Payloads para gráficas --}}

<script id="sexPayloadJSON" type="application/json">
{!! json_encode(
$sexPayload ?? ['labels' => ['Hombres','Mujeres','Niños'], 'data' => [0,0,0]],
JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) !!}
</script>

{{-- Dietas entregadas (polarArea, monochrome) --}}
<script id="dietPayloadJSON" type="application/json">
{!! json_encode(
    $dietPayload ?? ['labels' => [], 'data' => []],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) !!}
</script>

<script id="mealsStackedJSON" type="application/json">
{!! json_encode(
    $mealsStackedPayload ?? ['categories' => [], 'series' => []],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) !!}
</script>

<script id="pyramidPayloadJSON" type="application/json">
{!! json_encode(
    $pyramidPayload ?? ['categories' => [], 'data' => []],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) !!}
</script>



// ------ 

{{-- Calendario --}}
<script>
(function(){
'use strict';
if (!window.flatpickr) { console.error('[SIGENUH] Flatpickr no cargado'); return; }

const $input   = document.getElementById('dateRange');
const $clear   = document.getElementById('btnClearRange');
const $presets = document.querySelectorAll('.range-presets [data-range]');
const form     = document.getElementById('filtersForm');
if (!$input) return;

const pad = n => String(n).padStart(2,'0');
const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const today = () => { const t=new Date(); return new Date(t.getFullYear(),t.getMonth(),t.getDate()); };
const firstDayOfMonth=(y,m)=>new Date(y,m,1);
const lastDayOfMonth =(y,m)=>new Date(y,m+1,0);
const parsePair = v => (v && v.includes(' - ')) ? v.split(' - ').map(s=>s.trim()) : [];

const es = Object.assign({}, window.flatpickr.l10ns.es||{});
es.rangeSeparator = ' - ';

const mq = window.matchMedia('(min-width:768px)');
const fp = window.flatpickr($input,{
    mode:'range',
    dateFormat:'Y-m-d',
    allowInput:true,
    disableMobile:true,
    position:'auto',
    showMonths: mq.matches ? 2 : 1,
    locale: es,
    defaultDate: parsePair($input.value),
    onOpen: ()=> fp.set('showMonths', mq.matches ? 2 : 1),
    onClose: (selectedDates)=>{
    if (selectedDates && selectedDates.length===2){
        $input.value = `${fmt(selectedDates[0])} - ${fmt(selectedDates[1])}`;
        if (form) form.submit();
    }
    }
});

$input.addEventListener('focus', ()=>fp.open());
$input.addEventListener('click',  ()=>fp.open());

const onMQ = (e)=> fp.set('showMonths', e.matches ? 2 : 1);
if (mq.addEventListener) mq.addEventListener('change', onMQ); else mq.addListener(onMQ);

const setRange = (start,end)=>{
    fp.setDate([fmt(start), fmt(end)], true, 'Y-m-d');
    if (form) form.submit();
};

const presets = {
    today(){ const d=today(); setRange(d,d); },
    yesterday(){ const d=today(); d.setDate(d.getDate()-1); setRange(d,d); },
    last7(){ const end=today(); const start=new Date(end); start.setDate(start.getDate()-6); setRange(start,end); },
    last30(){ const end=today(); const start=new Date(end); start.setDate(start.getDate()-29); setRange(start,end); },
    thisMonth(){ const t=today(); setRange(firstDayOfMonth(t.getFullYear(),t.getMonth()), lastDayOfMonth(t.getFullYear(),t.getMonth())); },
    lastMonth(){
    const t=today(); const m=t.getMonth()-1; const y=(m<0)?t.getFullYear()-1:t.getFullYear(); const mm=(m+12)%12;
    setRange(firstDayOfMonth(y,mm), lastDayOfMonth(y,mm));
    },
    thisYear(){ const t=today(); setRange(new Date(t.getFullYear(),0,1), new Date(t.getFullYear(),11,31)); }
};

$presets.forEach(btn=>{
    btn.addEventListener('click', ()=>{
    $presets.forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const key = btn.getAttribute('data-range');
    if (presets[key]) presets[key]();
    });
});

if ($clear){
    $clear.addEventListener('click', ()=>{
    fp.clear();
    $presets.forEach(b=>b.classList.remove('active'));
    });
}
})();
</script>


// ------


{{-- Generos de pacientes (polarArea, ApexCharts) --}}
<script>
(function(){
'use strict';
const fmt = n => (typeof n === 'number' ? n.toLocaleString('es-VE') : String(n));

function renderSexChart(){
    const el = document.getElementById('chartBySex');
    if (!el || !window.ApexCharts) return;

    let payload = { labels:['Hombres','Mujeres','Niños'], data:[0,0,0] };
    const src = document.getElementById('sexPayloadJSON');
    if (src && src.textContent){
    try { payload = JSON.parse(src.textContent); }
    catch(e){ console.error('sexPayloadJSON inválido', e); }
    }

    // 2) Normalizar y asegurar que labels y series tengan la misma longitud
    const rawLabels = Array.isArray(payload.labels) ? payload.labels.map(String) : [];
    const rawData   = Array.isArray(payload.data)   ? payload.data.map(v => Number(v) || 0) : [];
    const L         = Math.min(rawLabels.length, rawData.length);
    const labels    = rawLabels.slice(0, L);
    const series    = rawData.slice(0, L);

    const total  = series.reduce((a,b)=>a+b, 0);
    const hasData = total > 0;

    if (window.__sexApex) window.__sexApex.destroy();

    window.__sexApex = new ApexCharts(el, {
    chart:  { type:'polarArea', height:'100%', toolbar:{ show:false } },
    labels: hasData ? labels : ['Sin datos'],
    series: hasData ? series : [1],
    stroke: { width:1 },
    legend:{
        position:'bottom',
        formatter: function (name, opts) {
        const v = opts.w.globals.series[opts.seriesIndex] || 0;
        return hasData ? `${name}: ${fmt(v)} (${(v*100/total).toFixed(1)}%)` : `${name}: ${fmt(v)}`;
        }
    },
    dataLabels:{
        enabled:true,
        formatter: function (_val, opts) {
        const v = opts.w.globals.series[opts.seriesIndex] || 0;
        return hasData ? `${fmt(v)} • ${(v*100/total).toFixed(1)}%` : fmt(v);
        }
    },
    tooltip:{
        y:{ formatter: v => hasData ? `${fmt(v)} (${(v*100/total).toFixed(1)}%)` : fmt(v) }
    },
    noData:{ text:'Sin datos' }
    });

    window.__sexApex.render();

    const hint = el.closest('.card')?.querySelector('.mini-hint');
    if (hint) hint.textContent = hasData ? `Total: ${fmt(total)} — Hombres/Mujeres/Niños` : 'Sin datos';
}

if (document.readyState !== 'loading') renderSexChart();
else document.addEventListener('DOMContentLoaded', renderSexChart);
})();
</script>



// ------




<!-- Dietas entregadas — Pie (ApexCharts) -->
<script>
(function(){
'use strict';
const fmt = n => (typeof n === 'number' ? n.toLocaleString('es-VE') : String(n));

function renderDietChart(){
    const el = document.getElementById('chartByDiet');
    if (!el || !window.ApexCharts) return;

    let payload = { labels: [], data: [] };
    const src = document.getElementById('dietPayloadJSON');
    if (src && src.textContent){
        try { payload = JSON.parse(src.textContent); }
        catch(e){ console.error('[SIGENUH] dietPayloadJSON inválido:', e); }
    }

    const rawLabels = Array.isArray(payload.labels) ? payload.labels.map(String) : [];
    const rawData   = Array.isArray(payload.data)   ? payload.data.map(v => Number(v) || 0) : [];
    const L         = Math.min(rawLabels.length, rawData.length);
    const labels    = rawLabels.slice(0, L);
    const counts    = rawData.slice(0, L);

    const total   = counts.reduce((a,b)=>a+b, 0);
    const hasData = total > 0;

    if (window.__dietApex) window.__dietApex.destroy();

    window.__dietApex = new ApexCharts(el, {
        chart:  { type: 'pie', height: '100%', toolbar: { show: false } },
        labels: hasData ? labels : ['Sin datos'],
        series: hasData ? counts : [1],

    dataLabels: {
        enabled: true,
        dropShadow: { enabled: false },
        formatter: function (percent, opts) {
        const v = opts.w.globals.series[opts.seriesIndex] || 0; 
            return hasData ? `${fmt(v)} • ${percent.toFixed(1)}%` : fmt(v);
        }
    },

    legend: {
        position: 'bottom',
        formatter: function (name, opts) {
            const v = opts.w.globals.series[opts.seriesIndex] || 0;
          const pct = hasData && total ? (v * 100 / total).toFixed(1) : '0.0';
            return `${name}: ${fmt(v)} • ${pct}%`;
        }
    },

    tooltip: {
        y: {
            formatter: function (v, { seriesIndex, w }) {
            if (!hasData) return fmt(v);
            const pct = total ? (v * 100 / total).toFixed(1) : '0.0';
            return `${fmt(v)} (${pct}%)`;
            }
        }
    },

    noData: { text: 'Sin datos' },
    responsive: [{ breakpoint: 768, options: { legend: { position: 'bottom' } } }]
    });

    window.__dietApex.render();

    const hint = el.closest('.card')?.querySelector('.mini-hint');
    if (hint) hint.textContent = hasData ? `Total: ${fmt(total)} (pacientes)` : 'Sin datos';
}

if (document.readyState !== 'loading') renderDietChart();
else document.addEventListener('DOMContentLoaded', renderDietChart);
})();
</script>




// ------


{{-- tiempos servicios --}}
<script>
(function(){
'use strict';

const fmt = n => (typeof n === 'number' ? n.toLocaleString('es-VE') : String(n));

function renderMealsStacked(){
    const el  = document.getElementById('chartMealsByService');
    if (!el || !window.ApexCharts) return;

    let payload = { categories: [], series: [] };
    const src = document.getElementById('mealsStackedJSON');
    if (src && src.textContent){
    try { payload = JSON.parse(src.textContent); }
    catch(e){ console.error('[SIGENUH] mealsStackedJSON inválido:', e); }
    }

    const categories = Array.isArray(payload.categories) ? payload.categories.map(String) : [];
    const seriesRaw  = Array.isArray(payload.series) ? payload.series : [];

    const series = seriesRaw
    .filter(s => s && typeof s.name === 'string' && Array.isArray(s.data))
    .map(s => ({
        name: String(s.name),
        data: s.data.map(v => Number(v) || 0).slice(0, categories.length)
    }));

    for (const s of series) {
    if (s.data.length < categories.length) {
        s.data = s.data.concat(Array(categories.length - s.data.length).fill(0));
    }
    }

    const totalAll = series.reduce((acc, s) => acc + s.data.reduce((a,b)=>a+b, 0), 0);
    const hasData  = categories.length > 0 && totalAll > 0;
    const dynamicHeight = Math.max(320, 28 * categories.length + 120);

    if (window.__mealsStackedApex) window.__mealsStackedApex.destroy();

    window.__mealsStackedApex = new ApexCharts(el, {
    chart: {
        type: 'bar',
        stacked: true,
        height: dynamicHeight,
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
        horizontal: true,
        dataLabels: {
            total: {
            enabled: true,
            offsetX: 0,
            style: { fontSize: '13px', fontWeight: 700 }
            }
        }
        }
    },
    stroke: { width: 1, colors: ['#fff'] },
    series: hasData ? series : [{ name: 'Sin datos', data: [1] }],
    xaxis: {
        categories: hasData ? categories : [''],
        labels: {
        formatter: function (val) { return fmt(Number(val) || 0); }
        },
        title: { text: '' }
    },
    yaxis: { title: { text: '' } },
    legend: {
        position: 'top',
        horizontalAlign: 'left',
        offsetX: 16
    },
    tooltip: {
        y: { formatter: function (val) { return fmt(Number(val) || 0); } }
    },
    fill: { opacity: 1 },
    noData: { text: 'Sin datos' }
    });

    window.__mealsStackedApex.render();

    const hint = el.closest('.card')?.querySelector('.mini-hint');
    if (hint) hint.textContent = hasData
    ? `Servicios: ${fmt(categories.length)} • Total registros: ${fmt(totalAll)}`
    : 'Sin datos';
}

if (document.readyState !== 'loading') renderMealsStacked();
else document.addEventListener('DOMContentLoaded', renderMealsStacked);
})();
</script>


// ------


{{-- servicios con mas pascientes atendidos --}}
<script>
(function(){
'use strict';
const fmt = n => (typeof n === 'number' ? n.toLocaleString('es-VE') : String(n));

function renderServicesPyramid(){
    const el = document.getElementById('chartServicesPyramid');
    if (!el || !window.ApexCharts) return;

    let payload = { categories: [], data: [] };
    const src = document.getElementById('pyramidPayloadJSON');
    if (src && src.textContent){
    try { payload = JSON.parse(src.textContent); }
    catch(e){ console.error('[SIGENUH] pyramidPayloadJSON inválido:', e); }
    }

    const rawCats = Array.isArray(payload.categories) ? payload.categories.map(String)   : [];
    const rawVals = Array.isArray(payload.data)       ? payload.data.map(v => +v || 0) : [];
    const L       = Math.min(rawCats.length, rawVals.length);

    let categories = rawCats.slice(0, L);
    let data       = rawVals.slice(0, L);

    const pairs = categories.map((label, i) => ({ label, value: data[i] }));
    pairs.sort((a,b) => b.value - a.value);
    categories = pairs.map(p => p.label);
    data       = pairs.map(p => p.value);

    const total   = data.reduce((a,b)=>a+b, 0);
    const hasData = total > 0;

    const dynamicHeight = Math.max(320, 28 * categories.length + 120);

    if (window.__svcPyramidApex) window.__svcPyramidApex.destroy();

    const options = {
    chart: {
        type: 'bar',
        height: dynamicHeight,
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
        horizontal: true,
        isFunnel: true,
        barHeight: '80%'
        }
    },
    series: hasData ? [{ name: 'Comidas', data }] : [{ name: 'Sin datos', data: [1] }],
    xaxis: {
        categories: hasData ? categories : [''],
        labels: { formatter: val => fmt(+val || 0) }
    },
    dataLabels: {
        enabled: true,
        dropShadow: { enabled: false },
        formatter: function (val, opts) {
        const v = opts.w.globals.series[0][opts.dataPointIndex] || 0;
          const pct = total ? (v * 100 / total).toFixed(1) : '0.0';
        return hasData ? `${fmt(v)} • ${pct}%` : fmt(v);
        }
    },
    tooltip: {
        y: { formatter: v => fmt(+v || 0) }
    },
    legend: { show: false },
    noData: { text: 'Sin datos' }
    };

    window.__svcPyramidApex = new ApexCharts(el, options);
    window.__svcPyramidApex.render();

    const hint = el.closest('.card')?.querySelector('.mini-hint');
    if (hint) {
    if (hasData && categories.length) {
        const top = categories[0];
        const topVal = data[0] || 0;
        hint.textContent = `Top: ${top} — ${fmt(topVal)} comidas (Total: ${fmt(total)})`;
    } else {
        hint.textContent = 'Sin datos';
    }
    }
}

if (document.readyState !== 'loading') renderServicesPyramid();
else document.addEventListener('DOMContentLoaded', renderServicesPyramid);
})();
</script>




@endsection



