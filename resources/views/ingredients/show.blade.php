<div class="container">
    <h1>{{ $ingredient->name }}</h1>
    <ul class="list-group mb-3">
        <li class="list-group-item"><b>Categoría:</b> {{ $ingredient->category }}</li>
        <li class="list-group-item"><b>Unidad:</b> {{ $ingredient->unit }}</li>
        <li class="list-group-item"><b>Estado:</b> {{ $ingredient->status ? 'Activo' : 'Inactivo' }}</li>
        <li class="list-group-item"><b>Notas:</b> {{ $ingredient->notes }}</li>
    </ul>
    <a href="{{ route('ingredients.index') }}" class="btn btn-secondary">Volver</a>
</div>
