<!-- START HEADER -->
<header class="app-header">
  <div class="container-fluid">
    <div class="nav-header">

      <div class="header-left hstack gap-3">
        <!-- HORIZONTAL BRAND LOGO -->
        <div class="app-sidebar-logo app-horizontal-logo justify-content-center align-items-center">
          <a href="{{ route('dashboard') }}">
            <img height="35" class="app-sidebar-logo-default" alt="Logo" loading="lazy" src="{{ asset('assets/images/light-log.png') }}">
            <img height="40" class="app-sidebar-logo-minimize" alt="Logo" loading="lazy" src="{{ asset('assets/images/16650.png') }}">
          </a>
        </div>

        <!-- Sidebar Toggle Btn -->
        <button type="button" class="btn btn-light-light icon-btn sidebar-toggle d-none d-md-block" aria-expanded="false" aria-controls="main-menu">
          <span class="visually-hidden">Toggle sidebar</span>
          <i class="ri-menu-2-fill"></i>
        </button>

        <!-- Sidebar Toggle for Mobile -->
        <button class="btn btn-light-light icon-btn d-md-none small-screen-toggle" id="smallScreenSidebarLabel" type="button" data-bs-toggle="offcanvas" data-bs-target="#smallScreenSidebar" aria-controls="smallScreenSidebar">
          <span class="visually-hidden">Sidebar toggle for mobile</span>
          <i class="ri-arrow-right-fill"></i>
        </button>

        <!-- Sidebar Toggle for Horizontal Menu -->
        <button class="btn btn-light-light icon-btn d-lg-none small-screen-horizontal-toggle" type="button" ria-expanded="false" aria-controls="main-menu">
          <span class="visually-hidden">Sidebar toggle for horizontal</span>
          <i class="ri-arrow-right-fill"></i>
        </button>

        <!-- Search Dropdown -->
        <div class="dropdown features-dropdown">
          <!-- Search Input for Desktop -->
          <form class="d-none d-sm-block header-search" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="form-icon">
            </div>
          </form>
          <div class="dropdown-menu">
            <span class="dropdown-header fs-12">Recent searches</span>
            <div class="dropdown-item text-wrap bg-transparent">
              <span class="badge bg-light text-muted me-2">Gulp</span>
              <span class="badge bg-light text-muted me-2">Notification panel</span>
            </div>
            <div class="dropdown-divider"></div>
            <span class="dropdown-header fs-12">Tutorials</span>
            <div class="dropdown-item">
              <div class="hstack gap-2 overflow-hidden">
                <button type="button" class="btn btn-light-light rounded-pill icon-btn-sm flex-shrink-0">
                  <span class="visually-hidden">Equalizer settings</span>
                  <i class="ri-equalizer-3-line m-0"></i>
                </button>
                <div class="flex-grow-1 text-truncate">
                  <span>How to set up Gulp?</span>
                </div>
              </div>
            </div>
            <div class="dropdown-item">
              <div class="hstack gap-2 overflow-hidden">
                <button type="button" class="btn btn-light-light rounded-pill icon-btn-sm flex-shrink-0">
                  <span class="visually-hidden">How to change theme color?</span>
                  <i class="ri-palette-line m-0"></i>
                </button>
                <div class="flex-grow-1 text-truncate">
                  <span>How to change theme color?</span>
                </div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <span class="dropdown-header fs-12">Members</span>
            <div class="dropdown-item">
              <div class="hstack gap-2 overflow-hidden">
                <div class="flex-shrink-0">
                  <img class="img-fluid avatar-sm avatar-item" src="{{ asset('assets/images/avatar/avatar-10.jpg') }}" alt="avatar image">
                </div>
                <div class="flex-grow-1 text-truncate">
                  <span>Amanda Harvey <i class="bi-patch-check-fill text-primary"></i></span>
                </div>
              </div>
            </div>
            <div class="dropdown-item">
              <div class="hstack gap-2 overflow-hidden">
                <div class="flex-shrink-0">
                  <img class="img-fluid avatar-sm avatar-item" src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="avatar image">
                </div>
                <div class="flex-grow-1 text-truncate">
                  <span>David Harrison</span>
                </div>
              </div>
            </div>
            <div class="dropdown-item">
              <div class="hstack gap-2 overflow-hidden">
                <div class="flex-shrink-0">
                  <div class="avatar-item avatar-sm avatar-title border-0 text-bg-info">A</div>
                </div>
                <div class="flex-grow-1 text-truncate ms-2">
                  <span>Anne Richard</span>
                </div>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <a class="px-5 py-2 d-block text-center link-primary">
              See all results
              <i class="ri-arrow-right-s-line"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="header-right hstack gap-3">
        <div class="hstack gap-0 gap-sm-1">
          <!-- Apps -->
          <div class="dropdown features-dropdown">
            <button type="button" class="btn icon-btn btn-text-primary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <span class="visually-hidden">Seleccione un hospital</span>
              <i class="bi bi-grid"></i>
            </button>
              <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end">
                  <div class="card shadow-none mb-0 border-0">
                      <div class="card-header hstack gap-2">
                          <h5 class="card-title mb-0 flex-grow-1">Seleccione un hospital</h5>
                      </div>
                      <div class="card-body px-3">
                          @foreach($hospitals_table as $h)
                          <div class="row g-0">
                              <div class="col">
                                  <form action="{{ route('user.select-hospital', $h->id) }}" method="POST">
                                      @csrf
                                      <button type="submit" id="btnSender{{ $h->id }}" style="visibility: hidden;">
                                      </button>
                                      <a class="dropdown-icon-item" onclick="document.getElementById('btnSender{{ $h->id }}').click();">
                                          <div class="avatar border-0 avatar-item bg-light mx-auto mb-2">
                                              <img src="storage/{{ $h->icon_path }}" />
                                          </div>
                                          <p class="mb-1 h6 fw-medium">{{ $h->name }}</p>
                                          <p class="mb-0 text-muted fs-11">{{ $h->description }}</p>
                                      </a>
                                  </form>

                              </div>
                          </div>
                          @endforeach
                      </div>
                  </div>
              </div>
          </div>
          <!-- Fullscreen -->
          <button type="button" id="fullscreen-button" class="btn icon-btn btn-text-primary rounded-circle custom-toggle d-none d-sm-block" aria-pressed="false">
            <span class="visually-hidden">Toggle Fullscreen</span>
            <span class="icon-on">
              <i class="ri-fullscreen-exit-line fs-16"></i>
            </span>
            <span class="icon-off">
              <i class="ri-fullscreen-line fs-16"></i>
            </span>
          </button>
        </div>
        <!-- Profile Section -->
        <div class="dropdown profile-dropdown features-dropdown">
          <button type="button" id="accountNavbarDropdown" class="btn profile-btn shadow-none px-0 hstack gap-0 gap-sm-3" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside" data-bs-dropdown-animation>
            <span class="position-relative">
              <span class="avatar-item avatar overflow-hidden">
                <img class="img-fluid" src="{{ asset(Auth::user()?->avatar_url ?? 'avatars/default.jpg') }}" alt="avatar image">
              </span>
              <span class="position-absolute border-2 border border-white h-12px w-12px rounded-circle bg-success end-0 bottom-0"></span>
            </span>
            <span>
              <span class="h6 d-none d-xl-inline-block text-start fw-semibold mb-0">{{ Auth::user()?->name ?? '' }}</span>
              <span class="d-none d-xl-block fs-12 text-start text-muted">CEO</span>
            </span>
          </button>
          <div class="dropdown-menu dropdown-menu-end header-language-scrollable" aria-labelledby="accountNavbarDropdown">
            <div class="dropdown dropstart d-block">
              <ul class="dropdown-menu">
                <li class="dropdown-item">
                  <span class="h-8px w-8px rounded-pill bg-success me-2"></span>
                  Available
                </li>
                <li class="dropdown-item">
                  <span class="h-8px w-8px rounded-pill bg-danger me-2"></span>
                  Busy
                </li>
                <li class="dropdown-item">
                  <span class="h-8px w-8px rounded-pill bg-warning me-2"></span>
                  Away
                </li>
                <li class="dropdown-divider"></li>
                <li class="dropdown-item">
                  Reset status
                </li>
              </ul>
            </div>
              <a class="dropdown-item" href="{{ route('logout') }}"
              onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              Cerrar sesión
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                  @csrf
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</header>
<!-- END HEADER -->
