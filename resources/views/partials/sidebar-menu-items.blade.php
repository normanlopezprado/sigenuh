<ul class="main-menu" id="all-menu-items" role="menu">
    <li class="menu-title" role="presentation" data-lang="hr-title-main">Reportes</li>

    <li class="slide">
        {{-- === DASHBOARDS === --}}
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-dashboard-line"></i></span>
            <span class="side-menu__label" data-lang="hr-dashboards">Dashboards</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>

        <ul class="slide-menu" role="menu">
        
            <li 
                class="slide">
                <a href=" {{ route('dashboard') }} " 
                class="side-menu__item" 
                role="menuitem" 
                data-lang="hr-dashboards-ecommerce">
                Calendario
                </a>
            </li>

            <li class="slide">
                <a 
                href="{{ route('dashboard.carts.index') }}" 
                data-lang="hr-dashboards-project-management" 
                class="side-menu__item" 
                role="menuitem">
                Carritos
                </a>
            </li>

            <li class="slide">
                <a 
                href="{{ route('dashboard.carts.index') }}" 
                data-lang="hr-dashboards-project-management" 
                class="side-menu__item" 
                role="menuitem">
                Carritos
                </a>
            </li>

        </ul>
    </li>

    {{-- === DEPARTAMENTO DE NUTRICIÓN === --}}
    
    <li class="menu-title" role="presentation" data-lang="hr-title-applications">Departamento de nutrición</li>
    @canany(['
            staff-beneficiaries.index',
            'staff-beneficiaries.create',
            'staff-beneficiaries.edit',
            'staff-beneficiaries.delete',

            

            'carts.index',
            'carts.routes.update',

            'menus.index', 
            'menus.create', 
            'menus.edit', 
            'menus.delete',

            'ingredients.index', 
            'ingredients.create', 
            'ingredients.edit', 
            'ingredients.delete'
            ])
        <li class="slide">
            <a href="#!" class="side-menu__item" role="menuitem">
                <span class="side_menu_icon"><i class="ri-empathize-line"></i></span>
                <span class="side-menu__label" data-lang="hr-apps">Nutrición</span>
                <i class="ri-arrow-down-s-line side-menu__angle"></i>
            </a>

            <ul class="slide-menu" role="menu">

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-line-chart-line"></i>
                        <span class="side-menu__label" data-lang="hr-apps-email">Estadísticas</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">

                        <li class="slide">
                            <a href="{{ route('stats.report') }}" 
                            class="side-menu__item" 
                            role="menuitem" 
                            data-lang="hr-apps-email-inbox">
                                <i class="ri-brain-line"></i>
                                Historia
                            </a>
                        </li>
                        
                    </ul>
                </li>
                
                
                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-team-line"></i>
                        <span 
                        class="side-menu__label" 
                        data-lang="hr-apps-email">
                        Beneficiarios
                        </span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>

                    <ul class="slide-menu" role="menu">

                        <li class="slide">
                            <a href="{{ route('staff_meals.report') }}" 
                            class="side-menu__item" 
                            role="menuitem" 
                            data-lang="hr-apps-email-inbox">
                                <i class="ri-file-chart-line"></i>
                                Reportes
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('staff-beneficiaries.index') }}" 
                            class="side-menu__item" 
                            role="menuitem" 
                            data-lang="hr-apps-email-inbox">
                                <i class="ri-user-settings-line"></i>
                                Gestionar
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-shopping-cart-line"></i>
                        <span 
                        class="side-menu__label" 
                        data-lang="hr-apps-email">
                        Carritos
                        </span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">
                        <li class="slide">
                            <a href="{{ route('carts.routes.update') }}" 
                            class="side-menu__item" 
                            role="menuitem" >
                                <i class="ri-guide-line"></i>
                                Rutas
                            </a>

                            <a href="{{ route('carts.index') }}" 
                            class="side-menu__item" 
                            role="menuitem">
                                <i class="ri-shopping-cart-2-line"></i>
                                Gestionar
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="slide">
                    <a href="#!" class="side-menu__item" role="menuitem">
                        <i class="ri-restaurant-2-line"></i>
                        <span class="side-menu__label" data-lang="hr-apps-email">Dietas</span>
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu" role="menu">

                        <li class="slide">
                            <a href="{{ route('menus.index') }}" 
                            class="side-menu__item" 
                            role="menuitem" 
                            data-lang="hr-apps-email-inbox">
                                <i class="ri-service-bell-line"></i>
                                Menús
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('ingredients.index') }}" 
                            class="side-menu__item" 
                            role="menuitem" 
                            data-lang="hr-apps-email-inbox">
                                <i class="ri-fridge-line"></i>
                                Ingredientes
                            </a>
                        </li>
                    </ul>
                </li>

                

            </ul>
            
        </li>
        @endcanany
        

    {{-- === RECOLECCIÓN === --}}
    @canany([
        'collects.index',
        'collects.bulk',
        'collects.toggle-bed',
        'collects.save-companion',
        ])
    <li class="menu-title" role="presentation" data-lang="hr-title-pages">Recolección</li>
    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-service-bell-line"></i></span>
            <span class="side-menu__label" data-lang="hr-pages">Dietas</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>
        <ul class="slide-menu" role="menu">
            <li class="slide">
                {{-- Ajusta la ruta si ya tienes la pantalla de recolección --}}
                <a href="{{ route('collects.cards') }}" 
                class="side-menu__item" 
                role="menuitem" 
                data-lang="hr-pages-start">
                    <i class="ri-keyboard-box-line"></i>
                    Recolectar
                </a>
            </li>
        </ul>
    </li>
    @endcanany

    {{-- === ENTREGAS === --}}
    @canany([
        'staff-meals.view'
        ])
    <li class="menu-title" role="presentation" data-lang="hr-title-pages">Entregas</li>
    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-team-line"></i></span>
            <span class="side-menu__label" data-lang="hr-pages">Beneficiarios</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>
        <ul class="slide-menu" role="menu">
            
            <li class="slide">
                <a href="{{ route('staff_meals.delivery') }}" 
                class="side-menu__item" 
                role="menuitem" 
                data-lang="hr-pages-start">
                    <i class="ri-bowl-line"></i>
                    Entregas
                </a>
            </li>

        </ul>
    </li>
    @endcanany

    <li class="menu-title" role="presentation" data-lang="hr-title-tables">Administración</li>
    {{-- === USUARIOS === --}}
    @canany(['
    users.index', 
    'users.create', 
    'users.edit', 
    'users.delete'
    ])
    <li class="slide">
        <a href="#!" class="side-menu__item" role="menuitem">
            <span class="side_menu_icon"><i class="ri-shield-keyhole-line"></i></span>
            <span class="side-menu__label" data-lang="hr-maps">Accesos</span>
            <i class="ri-arrow-down-s-line side-menu__angle"></i>
        </a>

        <ul class="slide-menu" role="menu">
            <li class="slide">
                <a href="{{ route('usuarios.index') }}" 
                class="side-menu__item" 
                role="menuitem" 
                data-lang="hr-layout-two-column">
                    <i class="ri-shield-user-line"></i>
                    Gestionar
                </a>
            </li>
        </ul>
        
    </li>
    @endcanany

    {{-- === HOSPITALES === --}}
    @canany(['
            beds.index', 
            'beds.create', 
            'beds.edit', 
            'beds.delete',

            'servicios.show',

            'hospital-floor-services.edit',

            'servicios.index', 
            'servicios.create', 
            'servicios.edit', 
            'servicios.delete', 
            'servicios.show',

            'hospitalfloors.edit', 

            'niveles.index', 
            'niveles.create', 
            'niveles.edit', 
            'niveles.delete',

            'hospitales.index', 
            'hospitales.create', 
            'hospitales.edit', 
            'hospitales.delete'
            ])
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
                    
                    <li class="slide">
                        <a href="{{ route('beds.index') }}" 
                        class="side-menu__item" 
                        role="menuitem" 
                        data-lang="hr-layout-two-column">
                            <i class="ri-hotel-bed-line"></i>
                            Camas
                        </a>
                    </li>
                    
                    <li class="slide">
                        <a href="{{ route('hospital-floor-services.edit') }}" 
                        class="side-menu__item" 
                        role="menuitem" 
                        data-lang="hr-layout-two-column">
                            <i class="ri-link-unlink-m"></i>
                            Vincular
                        </a>
                    </li>

                    <li class="slide">
                        <a href="{{ route('servicios.index') }}" 
                        class="side-menu__item" 
                        role="menuitem" 
                        data-lang="hr-layout-two-column">
                            <i class="ri-heart-add-line"></i>
                            Servicios
                        </a>
                    </li>

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
                        <a href="{{ route('hospital-floors.edit') }}" 
                        class="side-menu__item" 
                        role="menuitem" 
                        data-lang="hr-vector-maps">
                            <i class="ri-link-unlink-m"></i>
                            Vincular
                        </a>
                    </li>

                    <li class="slide">
                        <a href="{{ route('niveles.index') }}" 
                        class="side-menu__item" 
                        role="menuitem" 
                        data-lang="hr-layout-two-column">
                            <i class="ri-building-line"></i>
                            Plantas
                        </a>
                    </li>
                    
                </ul>
            </li>
        </ul>
        <ul class="slide-menu" role="menu">
            <li class="slide">
                <a href="{{ route('hospitales.index') }}" 
                class="side-menu__item" 
                role="menuitem" 
                data-lang="hr-vector-maps">
                    <i class="ri-hospital-line"></i>
                    Gestionar
                </a>
            </li>
            
        </ul>
    </li>
    @endcanany
</ul>
