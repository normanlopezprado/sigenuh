@extends('partials.layouts.master2')

@section('title', 'SIGENUH')
@section('sub-title', 'Accesos -> Usuarios -> Crear usuario')
@section('pagetitle', 'Inicio')
@section('buttonTitle', 'Share')
@section('modalTarget', 'shareModal')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/libs/air-datepicker/air-datepicker.css') }}">
@endsection

@section('content')
    <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
        @csrf
        @include('usuarios.form', ['usuario' => new \App\Models\User()])
    </form>
@endsection

@section('js')
    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/air-datepicker/air-datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/form/form-layout.init.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>

<script>
    
    document.addEventListener("DOMContentLoaded", function () {
    const nameInput = document.getElementById('name');
    const userInput = document.getElementById('user');
        
    // ✅ Nunca será "undefined" aunque no exista $usuario
    const isEdit = @json(isset($usuario) && $usuario->exists);

    let userManuallyEdited = false;

    function limpiar(texto) {
        return texto
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9\s]/g, " ")
        .trim();
    }

    function generarUsername(nombre) {
        if (!nombre) return '';
        const parts = limpiar(nombre).split(/\s+/).filter(Boolean);
        if (parts.length === 1) return parts[0].substring(0,4);
        if (parts.length === 2) return parts[0].substring(0,2) + parts[1].substring(0,2);
        if (parts.length === 3) return parts[0].substring(0,2) + parts[1].substring(0,2) + parts[2].substring(0,2);
        return parts[0].substring(0,2)
            + parts[1].substring(0,2)
            + parts[parts.length-2].substring(0,2)
            + parts[parts.length-1].substring(0,2);
    }

    userInput.addEventListener('input', () => { userManuallyEdited = true; });

    function actualizar() {
        const nombre = nameInput?.value?.trim() ?? '';
        if (nombre === '') {
        userInput.value = '';
        userManuallyEdited = false; // reset cuando vacían el nombre
        return;
        }
        if (!userManuallyEdited) {
        userInput.value = generarUsername(nombre);
        }
    }

    nameInput?.addEventListener('input', actualizar);
    nameInput?.addEventListener('blur', actualizar);

    // Prefill al cargar: solo si estamos creando y el campo está vacío
    if (!isEdit && (userInput?.value?.trim() === '') && (nameInput?.value?.trim() !== '')) {
        actualizar();
    }
    });
    

</script>
@endsection
