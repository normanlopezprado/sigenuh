@extends('partials.layouts.master-auth')

@section('title', 'Reset Password | Herozi - The Worlds Best Selling Bootstrap Admin & Dashboard Template by SRBThemes')

@section('css')
    @include('partials.head-css', ['auth' => 'layout-auth'])
@endsection

@section('content')
    <!-- START -->
    <div class="account-pages">
        <img src="{{ asset('assets/images/auth/auth_bg.jpeg') }}" alt="auth_bg" class="auth-bg light">
        <img src="{{ asset('assets/images/auth/auth_bg_dark.jpg') }}" alt="auth_bg_dark" class="auth-bg dark">
        <div class="container">
            <div class="justify-content-center row gy-0">

                <div class="col-lg-6 auth-banners">
                    <div class="bg-login card card-body m-0 h-100 border-0">
                        <img src="{{ asset('assets/images/auth/bg-img-2.png') }}" class="img-fluid auth-banner"
                            alt="auth-banner">
                        <div class="auth-contain">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                        class="active" aria-current="true" aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                        aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                        aria-label="Slide 3"></button>
                                </div>
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <div class="text-center text-white my-4 p-4">
                                            <h3 class="text-white">Learn and Practice</h3>
                                            <p class="mt-3">Manage your application seamlessly. Log in to access your
                                                dashboard and configure settings.</p>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="text-center text-white my-4 p-4">
                                            <h3 class="text-white">Secure Your Data</h3>
                                            <p class="mt-3">Ensure your application remains secure. Utilize our tools to
                                                monitor and protect your data effectively.</p>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="text-center text-white my-4 p-4">
                                            <h3 class="text-white">User Management</h3>
                                            <p class="mt-3">Easily manage users, roles, and permissions. Streamline your
                                                admin tasks with our intuitive interface.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="auth-box card card-body m-0 h-100 border-0 justify-content-center">
                        <div class="mb-5 text-center">
                            <h4 class="fw-medium">Forgot Password?</h4>
                            <p class="text-muted mb-0"> Simply enter your email address below, and we’ll send you a link to
                                reset your password.</p>
                        </div>
                        <form class="form-custom mt-10">

                            <div class="mb-5">
                                <label class="form-label" for="login-email">Email<span class="text-danger ms-1">*</span>
                                </label>
                                <input type="text" class="form-control" id="login-email" placeholder="Enter your email">
                            </div>

                            <a class="text-center mt-5 pt-5" href="auth-two-step-verify">
                                <button type="button" class="btn btn-primary rounded-2 w-100 btn-loader">
                                    <span class="indicator-label">
                                        Send Reset Link
                                    </span>
                                    <span class="indicator-progress flex gap-2 justify-content-center w-100">
                                        <span>Please Wait...</span>
                                        <i class="ri-loader-2-fill"></i>
                                    </span>
                                </button>
                            </a>
                            <div class="center-hr my-10 text-nowrap text-muted">Or with email</div>

                            <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                                <button type="button" class="btn btn-outline-facebook icon-btn">
                                    <i class="ri-facebook-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-google icon-btn">
                                    <i class="ri-google-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-twitter icon-btn">
                                    <i class="ri-twitter-fill"></i>
                                </button>
                                <button type="button" class="btn btn-outline-instagram icon-btn">
                                    <i class="ri-instagram-fill"></i>
                                </button>
                            </div>
                            <p class="mb-0 mt-5 text-muted text-center">
                                I Remember!, Let me try once...
                                <a href="auth-signin" class="text-primary fw-medium text-decoraton-underline ms-1">
                                    Sign In
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
    <!-- App js -->
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
@endsection
