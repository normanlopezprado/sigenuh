<div class="container">
    <h1>Editar ingrediente</h1>

    <form method="POST" action="{{ route('ingredients.update', $ingredient) }}">
        @csrf
        @method('PUT')
        @include('ingredients.form')
        <button class="btn btn-success">Actualizar</button>
        <a href="{{ route('ingredients.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
