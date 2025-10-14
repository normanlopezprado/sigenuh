@extends('partials.layouts.master-auth')
@include('partials.head-css', ['auth' => 'layout-auth'])
@section('css')
@section('title', 'Iniciar sesión | SIGENUH')
@endsection

@section('content')
    <div class="account-pages">
        <img src="{{ asset('assets/images/auth/auth_bg.jpeg') }}" alt="auth_bg" class="auth-bg light">
        <img src="{{ asset('assets/images/auth/auth_bg_dark.jpg') }}" alt="auth_bg_dark" class="auth-bg dark">
        <div class="container">
            <div class="justify-content-center row gy-0">
                <div class="col-lg-6">
                    <div class="auth-box card card-body m-0 h-100 border-0 justify-content-center">
                        <div class="mb-5 text-center">
                            <h4 class="fw-normal">Bienvenido a <span class="fw-bold text-primary">SIGENUH</span></h4>
                            <p class="text-muted mb-0">Sistema de gestión nutricional hospitalaria.</p>
                        </div>
                        <form class="form-custom mt-10" method="POST" action="{{ route('login') }}" >
                            @csrf
                            <div class="mb-5">
                                <label class="form-label" for="login-email">Usuario<span class="text-danger ms-1">*</span>
                                </label>
                                <input type="text" class="form-control" id="login-email" name="user" required  placeholder="Ingresa tu usuario">
                            </div>

                            <div class="mb-5">
                                <label class="form-label" for="LoginPassword">Contraseña<span
                                        class="text-danger ms-1">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="LoginPassword" class="form-control" name="password"
                                        required placeholder="Ingresa tu contraseña" data-visible="false">
                                    <a class="input-group-text bg-transparent toggle-password" href="javascript:;"
                                    data-target="password">
                                        <i class="ri-eye-off-line text-muted toggle-icon"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="row mb-5">
                                <div class="col-sm-6">
                                    <div class="form-check form-check-sm d-flex align-items-center gap-2 mb-0">
                                        <input class="form-check-input" type="checkbox" value="remember-me"
                                            id="remember-me">
                                        <label class="form-check-label" for="remember-me">
                                            Recordarme
                                        </label>
                                    </div>
                                </div>
                                <a href="{{ route('password.request') }}" class="col-sm-6 text-end">
                                    <span class="fs-14 text-muted">
                                        ¿Olvidaste tu contraseña?
                                    </span>
                                </a>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-2 w-100 btn-loader">
                                    <span class="indicator-label">
                                        Iniciar sesión
                                    </span>
                                    <span class="indicator-progress flex gap-2 justify-content-center w-100">
                                        <span>Please Wait...</span>
                                        <i class="ri-loader-2-fill"></i>
                                    </span>
                                </button>
                            <p class="mb-0 mt-5 text-muted text-center">
                                Todavía no tienes una cuenta? ponte en contacto con IT
                                </a>
                            </p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
