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
  document.addEventListener("DOMContentLoaded", function() {
      const nameInput = document.getElementById('name');
      const userInput = document.getElementById('user');

      function generarUsername(nombre) {
          if (!nombre) return '';

          // Normalizar: minúsculas, sin acentos
          nombre = nombre.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
          let parts = nombre.trim().split(/\s+/).filter(Boolean);

          let username = '';

          if (parts.length === 2) {
              // 1 nombre + 1 apellido
              username = parts[0].substring(0,2) + parts[1].substring(0,2);
          } else if (parts.length === 3) {
              // 1 nombre + 2 apellidos
              username = parts[0].substring(0,2) + parts[1].substring(0,2) + parts[2].substring(0,2);
          } else if (parts.length >= 4) {
              // Solo 2 nombres + 2 apellidos
              username = parts[0].substring(0,2) + parts[1].substring(0,2)
                       + parts[parts.length-2].substring(0,2) + parts[parts.length-1].substring(0,2);
          } else {
              // fallback: primer nombre hasta 4 letras
              username = parts[0].substring(0,4);
          }

          return username;
      }

      function actualizar() {
          const generado = generarUsername(nameInput.value);
          userInput.value = generado;
          console.log("Generado:", generado);
      }

      // Generar username en tiempo real y también al perder foco
      nameInput.addEventListener('input', actualizar);
      nameInput.addEventListener('blur', actualizar);
  });
  </script>
@endsection
