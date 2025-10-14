
    <div class="container">
        <h1>Detalle de la Cama</h1>

        <ul class="list-group">
            <li class="list-group-item"><b>Número:</b> {{ $bed->code }}</li>
            <li class="list-group-item"><b>Estado:</b> {{ $bed->status }}</li>
            <li class="list-group-item"><b>Notas:</b> {{ $bed->notes }}</li>
            <li class="list-group-item"><b>HospitalFloorService:</b> {{ $bed->hospitalFloorService?->id }}</li>
        </ul>

        <a href="{{ route('beds.index') }}" class="btn btn-secondary mt-3">Volver</a>
    </div>
