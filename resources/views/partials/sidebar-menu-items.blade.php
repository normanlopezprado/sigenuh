<ul class="main-menu" id="all-menu-items" role="menu">
    <li class="menu-title" role="presentation" data-lang="hr-title-main">Reportes</li>
    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-dashboard-line"></i></span>
            <span class="side-menu__label" data-lang="hr-dashboards">Dashboards</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>
        <ul class="slide-menu" role="menu">
            <li class="slide">
                <a href="index" class="side-menu__item" role="menuitem" data-lang="hr-dashboards-ecommerce">Resumen diario</a>
            </li>
            <li class="slide">
                <a href="dashboard-project-management" data-lang="hr-dashboards-project-management" class="side-menu__item" role="menuitem">Historial</a>
            </li>
        </ul>
    </li>

    <li class="menu-title" role="presentation" data-lang="hr-title-applications">Departamento de nutrición</li>
        <li class="slide">
            <a href="#!" class="side-menu__item" role="menuitem">
                <span class="side_menu_icon"><i class="ri-empathize-line"></i></span>
                <span class="side-menu__label" data-lang="hr-apps">Nutrición</span>
                <i class="ri-arrow-down-s-line side-menu__angle"></i>
            </a>

            <ul class="slide-menu" role="menu">

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-keyboard-box-line"></i>
                        <span class="side-menu__label" data-lang="hr-apps-email">Recolección</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">
                        <li class="slide">
                            <a href="#" class="side-menu__item" role="menuitem" data-lang="hr-apps-email-inbox">Iniciar</a>
                        </li>
                    </ul>
                </li>

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-user-smile-line"></i>
                        <span class="side-menu__label" data-lang="hr-apps-email">Beneficiarios</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">
                        <li class="slide">
                            <a href="#" class="side-menu__item" role="menuitem" data-lang="hr-apps-email-inbox">
                                <i class="ri-settings-4-line"></i>
                                Gestionar
                            </a>
                        </li>
                    </ul>
                </li>

                

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-shopping-cart-line"></i>
                        <span class="side-menu__label" data-lang="hr-apps-email">Carritos</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">
                        <li class="slide">
                            <a href="#" class="side-menu__item" role="menuitem" data-lang="hr-apps-email-inbox">
                                <i class="ri-settings-4-line"></i>
                                Gestionar
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-circle-line"></i>
                        <span class="side-menu__label" data-lang="hr-apps-email">Dietas</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">
                        
                    
                        @canany(['menus.index', 'menus.create', 'menus.edit', 'menus.delete'])
                        <li class="slide">
                            <a href="{{ route('menus.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-apps-email-inbox">
                                <i class="ri-service-bell-line"></i>
                                Gestionar Menús
                            </a>
                        </li>
                        @endcan

                        @canany(['ingredients.index', 'ingredients.create', 'ingredients.edit', 'ingredients.delete'])
                        <li class="slide">
                            <a href="{{ route('ingredients.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-apps-email-inbox">
                                <i class="ri-restaurant-2-line"></i>
                                Ingredientes
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
        </ul>
    </li>
    <li class="menu-title" role="presentation" data-lang="hr-title-pages">Recolección - Reparto</li>
    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-list-check-3"></i></span>
            <span class="side-menu__label" data-lang="hr-pages">Dietas</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>
        <ul class="slide-menu" role="menu">

            <li class="slide">
                <a href="#" class="side-menu__item" role="menuitem" data-lang="hr-pages-start">
                    <i class="ri-keyboard-box-line"></i>
                    Recolectar
                </a>
            </li>

            <li class="slide">
                <a href="#" class="side-menu__item" role="menuitem" data-lang="hr-pages-start">
                    <i class="ri-user-smile-line"></i>
                    Beneficiarios
                </a>
            </li>

        </ul>
    </li>

    <li class="menu-title" role="presentation" data-lang="hr-title-tables">Administración</li>


    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-shield-keyhole-line"></i></span>
            <span class="side-menu__label" data-lang="hr-maps">Accesos</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>

            <ul class="slide-menu" role="menu">
                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <span><i class="ri-group-line"></i></span>
                        <span class="side-menu__label" data-lang="hr-level-2-2">Usuarios</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>

                    @canany(['users.index', 'users.create', 'users.edit', 'users.delete'])
                    <ul class="slide-menu" role="menu">
                        <li class="slide">
                            <a href="{{ route('usuarios.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-layout-two-column">
                                <i class="ri-shield-user-line"></i>
                                Gestionar
                            </a>
                        </li>
                    </ul>
                    @endcan
                </li>

            </ul>
    </li>

    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-hospital-line"></i></span>
            <span class="side-menu__label" data-lang="hr-maps">Hospitales</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>

        <ul class="slide-menu" role="menu">
        </ul>
        <ul class="slide-menu" role="menu">
            <li class="slide">
                <a href="#!" class="side-menu__item" role="menuitem">
                    <span><i class="ri-service-line"></i></span>
                    <span class="side-menu__label" data-lang="hr-level-2-2">Servicios</span>
                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu" role="menu">
                    @canany(['beds.index', 'beds.create', 'beds.edit', 'beds.delete', 'servicios.show'])
                    <li class="slide">
                        <a href="{{ route('beds.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-layout-two-column">
                            <i class="ri-hotel-bed-line"></i>
                            Camas
                        </a>
                    </li>
                    @endcan

                    @can('hospital-floor-services.edit')
                    <li class="slide">
                        <a href="{{ route('hospital-floor-services.edit') }}" class="side-menu__item" role="menuitem" data-lang="hr-layout-two-column">
                            <i class="ri-link-unlink-m"></i>
                            Vincular
                        </a>
                    </li>
                    @endcan

                    @canany(['servicios.index', 'servicios.create', 'servicios.edit', 'servicios.delete', 'servicios.show'])
                    <li class="slide">
                        <a href="{{ route('servicios.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-layout-two-column">
                            <i class="ri-heart-add-line"></i>
                            Servicios
                        </a>
                    </li>
                    @endcan

                </ul>
            </li>

            <li class="slide">
                <a href="#!" class="side-menu__item" role="menuitem">
                    <span class="side_menu_icon"><i class="ri-building-4-line"></i></span>
                    <span class="side-menu__label" data-lang="hr-layout">Plantas</span>
                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu" role="menu">
                    <li class="slide">
                        @can('hospitalfloors.edit')
                        <a href="{{ route('hospital-floors.edit') }}" class="side-menu__item" role="menuitem" data-lang="hr-vector-maps">
                            <i class="ri-link-unlink-m"></i>
                            Vincular
                        </a>
                        @endcan
                    </li>
                    @canany(['niveles.index', 'niveles.create', 'niveles.edit', 'niveles.delete'])
                    <li class="slide">
                        <a href="{{ route('niveles.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-layout-two-column">
                            <i class="ri-building-line"></i>
                            Plantas
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
        </ul>

        <ul class="slide-menu" role="menu">
            @canany(['hospitales.index', 'hospitales.create', 'hospitales.edit', 'hospitales.delete'])
            <li class="slide">
                <a href="{{ route('hospitales.index') }}" class="side-menu__item" role="menuitem" data-lang="hr-vector-maps">
                    <i class="ri-hospital-line"></i>
                    Gestionar
                </a>
            </li>
            @endcan
        </ul>

    </li>
</ul>
