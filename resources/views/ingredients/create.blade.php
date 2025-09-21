<div class="container">
    <h1>Nuevo ingrediente</h1>

    <form method="POST" action="{{ route('ingredients.store') }}">
        @include('ingredients.form')
        <button class="btn btn-success">Guardar</button>
        <a href="{{ route('ingredients.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
