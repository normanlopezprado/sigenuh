<!-- START SIDEBAR -->
<aside class="app-sidebar">
    <!-- START BRAND LOGO -->
    <div class="app-sidebar-logo px-6 justify-content-center align-items-center">
        <a href="{{ route('dashboard') }}">
            <img height="35" class="app-sidebar-logo-default" alt="Logo" src="{{ asset('assets/images/light-log.png') }}">
            <img height="40" class="app-sidebar-logo-minimize" alt="Logo" src="{{ asset('assets/images/16650.png') }}">
        </a>
    </div>
    <!-- END BRAND LOGO -->
    <nav class="app-sidebar-menu nav nav-pills flex-column fs-6" id="sidebarMenu" aria-label="Main navigation">
        @include('partials.sidebar-menu-items')
    </nav>
</aside>
<!-- END SIDEBAR -->
<div class="horizontal-overlay"></div>

<!-- START SMALL SCREEN SIDEBAR -->
<div class="offcanvas offcanvas-md offcanvas-start small-screen-sidebar" data-bs-scroll="true" tabindex="-1" id="smallScreenSidebar" aria-labelledby="smallScreenSidebarLabel">
    <div class="offcanvas-header hstack border-bottom">
        <!-- START BRAND LOGO -->
        <div class="app-sidebar-logo">
            <a href="{{ route('dashboard') }}">
                <img height="35" class="app-sidebar-logo-default h-25px" alt="Logo" src="{{ asset('assets/images/light-log.png') }}">">
                <img height="40" class="app-sidebar-logo-minimize" alt="Logo" src="{{ asset('assets/images/16650.png') }}">">
            </a>
        </div>
        <button type="button" class="btn-close bg-transparent" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="ri-close-line"></i>
        </button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- START SIDEBAR -->
        <aside class="app-sidebar">
            <!-- END BRAND LOGO -->
            <nav class="app-sidebar-menu nav nav-pills flex-column fs-6" aria-label="Main navigation">
                @include('partials.sidebar-menu-items')

            </nav>
        </aside>
    </div>
</div>
