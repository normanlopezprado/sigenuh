<div class="container">
    <h1>Ingredientes</h1>

    <a href="{{ route('ingredients.create') }}" class="btn btn-primary mb-3">➕ Nuevo ingrediente</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Unidad</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        @forelse($ingredients as $ing)
            <tr>
                <td><a href="{{ route('ingredients.show', $ing) }}">{{ $ing->name }}</a></td>
                <td>{{ $ing->category }}</td>
                <td>{{ $ing->unit }}</td>
                <td>
                    @if($ing->status)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('ingredients.edit', $ing) }}" class="btn btn-sm btn-warning">✏️ Editar</a>
                    <form action="{{ route('ingredients.destroy', $ing) }}" method="POST" style="display:inline-block">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar ingrediente?')">🗑️ Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No hay ingredientes.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $ingredients->links() }}
</div>
