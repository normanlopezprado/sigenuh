<div class="container py-4">

    <h1 class="mb-3">Nuevo calendario</h1>
    <p class="text-muted mb-4">
        Selecciona la fecha del calendario. Los ingredientes opcionales del menú se agregan después, en la pantalla de edición.
    </p>

    {{-- mensajes de éxito/alerta --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    {{-- errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('calendars.store') }}">
                @csrf

                {{-- Fecha (precargada si vienes desde el calendario con ?date=YYYY-MM-DD) --}}
                <div class="mb-3">
                    <label for="date" class="form-label">Fecha</label>
                    <input
                        id="date"
                        type="date"
                        name="date"
                        class="form-control @error('date') is-invalid @enderror"
                        value="{{ old('date', $date ?? '') }}"
                        min="{{ now()->toDateString() }}"  {{-- evita fechas pasadas; quita si no lo quieres --}}
                        required
                    >
                    @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">La fecha mínima es hoy. Si necesitas permitir fechas pasadas, elimina el atributo <code>min</code>.</small>
                </div>

                {{-- Notas opcionales --}}
                <div class="mb-3">
                    <label for="notes" class="form-label">Notas (opcional)</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="form-control @error('notes') is-invalid @enderror"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        Guardar y continuar
                    </button>
                    <a href="{{ route('calendars.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Ayuda: qué sigue después --}}
    <div class="mt-4">
        <div class="alert alert-info mb-0">
            <strong>¿Qué sigue?</strong> Después de guardar, te llevaremos a <em>Editar calendario</em> para agregar los
            <u>ingredientes opcionales</u> (provenientes de <code>menu_ingredient</code> marcados como opcionales).
        </div>
    </div>

</div>

{{-- Opcional: si quieres bloquear fechas pasadas también del lado JS cuando se prellena --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('date');
        // Si llegó una fecha por query y es pasada, quita el min para permitirla (por si la necesitas)
        const passed = input.value && input.value < input.min;
        if (passed) input.removeAttribute('min');
    });
</script>
