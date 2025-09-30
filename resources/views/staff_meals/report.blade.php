@extends('partials.layouts.master')

@section('title', 'Inicio')
@section('pagetitle', 'Inicio')
@section('sub-title', 'Reporte de entregas')


@section('content')

<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Filtros</h5>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('staff_meals.report') }}" class="row g-3">
      {{-- Rango de fechas --}}
      <div class="col-md-3">
        <label class="form-label">Desde</label>
        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Hasta</label>
        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
      </div>

      {{-- Tiempo de comida --}}
      <div class="col-md-3">
        <label class="form-label">Tiempo de comida</label>
        <select name="meal_type" class="form-select">
          <option value="">— Todos —</option>
          @foreach($mealTypes as $mt)
            <option value="{{ $mt }}" @selected($filters['meal_type']===$mt)>{{ ucfirst($mt) }}</option>
          @endforeach
        </select>
      </div>

      {{-- Tipo de dieta --}}
      <div class="col-md-3">
        <label class="form-label">Tipo de dieta</label>
        <select name="diet_type" class="form-select">
          <option value="">— Todas —</option>
          @foreach($dietTypes as $dt)
            <option value="{{ $dt }}" @selected($filters['diet_type']===$dt)>{{ $dt }}</option>
          @endforeach
        </select>
      </div>

      {{-- Beneficiario contiene --}}
      <div class="col-md-3">
        <label class="form-label">Beneficiario (contiene)</label>
        <input type="text" name="benef_q" class="form-control" value="{{ $filters['benef_q'] }}">
      </div>

      {{-- Menú contiene --}}
      <div class="col-md-3">
        <label class="form-label">Menú (contiene)</label>
        <input type="text" name="menu_q" class="form-control" value="{{ $filters['menu_q'] }}">
      </div>

      {{-- Entregado por (lista sugerida) --}}
      <div class="col-md-3">
        <label class="form-label">Entregado por</label>
        <input list="deliveredNames" name="delivered_q" class="form-control" value="{{ $filters['delivered_q'] }}" placeholder="Nombre…">
        <datalist id="deliveredNames">
          @foreach($deliveredNames as $name)
            <option value="{{ $name }}"></option>
          @endforeach
        </datalist>
      </div>

      {{-- Tamaño de página --}}
      <div class="col-md-3">
        <label class="form-label">Filas por página</label>
        <select name="per_page" class="form-select">
          @foreach([25,50,100,200] as $pp)
            <option value="{{ $pp }}" @selected((int)$filters['per_page']===$pp)>{{ $pp }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="ri-filter-2-line"></i> Aplicar filtros
        </button>
        <a href="{{ route('staff_meals.report') }}" class="btn btn-outline-secondary">Reiniciar</a>

        {{-- Export CSV conserva filtros --}}
        <a href="{{ route('staff_meals.report', array_merge($filters, ['export'=>'csv'])) }}"
           class="btn btn-success ms-auto">
          <i class="ri-file-excel-2-line"></i> Exportar CSV
        </a>
      </div>
    </form>
  </div>
</div>

{{-- Resumen rápido --}}
<div class="mb-3 d-flex gap-2 flex-wrap">
  <span class="badge bg-dark">Total: {{ number_format($rows->total()) }}</span>
  @php
    $sumD = $summary['desayuno'] ?? 0;
    $sumA = $summary['almuerzo'] ?? 0;
    $sumC = $summary['cena'] ?? 0;
  @endphp
  <span class="badge bg-primary">Desayunos: {{ $sumD }}</span>
  <span class="badge bg-warning text-dark">Almuerzos: {{ $sumA }}</span>
  <span class="badge bg-info text-dark">Cenas: {{ $sumC }}</span>
</div>

{{-- Tabla --}}
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Resultados</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th style="width:36px;">#</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Tiempo</th>
            <th>Beneficiario</th>
            <th>Menú</th>
            <th>Dieta</th>
            <th>Entregado por</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $i => $r)
            <tr>
              <td>{{ ($rows->currentPage()-1)*$rows->perPage() + $i + 1 }}</td>
              <td>{{ \Carbon\Carbon::parse($r->delivery_date)->format('Y-m-d') }}</td>
              <td>{{ optional($r->delivered_at)->format('H:i:s') }}</td>
              <td>{{ ucfirst($r->meal_type) }}</td>
              <td>{{ $r->beneficiary }}</td>
              <td>{{ $r->menu_name ?? '—' }}</td>
              <td>{{ $r->diet_type ?? '—' }}</td>
              <td>{{ $r->delivered_by_name ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-muted">No hay registros para los filtros seleccionados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-2">
      {{ $rows->links() }}
    </div>
  </div>
</div>
@endsection
