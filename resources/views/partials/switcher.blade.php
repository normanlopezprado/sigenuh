<!-- START SWITCHER -->
<div class="switcher">
  <button type="button" class="switcher-icon btn btn-dark" data-bs-toggle="offcanvas" data-bs-target="#switcher">
    <i class="bi-sliders fs-6 me-2"></i> Interfaz
  </button>

  <!-- OFFCANVAS -->
  <div class="offcanvas offcanvas-end" id="switcher" tabindex="-1" aria-labelledby="switcherLabel">
    <div class="offcanvas-header border-bottom hstack">
      <h1 class="offcanvas-title fs-5 flex-grow-1" id="switcherLabel">Preview Settings</h1>
      <button type="button" class="close btn btn-text-primary icon-btn-sm flex-shrink-0" data-bs-dismiss="offcanvas" aria-label="Close">
        <i class="ri-close-large-line fw-semibold"></i>
      </button>
    </div>
    <div class="offcanvas-body">

      <!-- MAIN_LAYOUT -->
      <div class="d-none d-lg-block">
        <h6 class="mb-2 fs-5">Orientación de menú</h6>
        <p class="text-muted">Defina la orientación de la barra lateral del menú.</p>
        <div class="row g-4 mb-5">
          <div class="col-12 col-sm-4">
            <!-- VERTICAL -->
            <input class="form-check-input d-none" data-attribute="data-main-layout" name="layoutsModes" value="vertical" type="radio" id="verticalLayouts">
            <label for="verticalLayouts" class="switcher-card w-100">
              <span class="border d-block rounded h-100px overflow-hidden">
                <span class="d-flex h-100">
                  <span class="w-30px d-flex flex-column h-100 flex-shrink-0 border-end">
                    <span class="h-16px flex-shrink-0 bg-light d-block"></span>
                    <span class="h-100 flex-grow-1 bg-primary-subtle d-flex flex-column justify-content-between p-1">
                      <span>
                        <span class="h-6px bg-light rounded d-block mb-1"></span>
                        <span class="h-6px bg-light rounded d-block mb-1"></span>
                      </span>
                      <span class="h-6px bg-light rounded d-block mb-1"></span>
                    </span>
                  </span>
                  <span class="d-flex flex-column flex-grow-1">
                    <span class="px-2 flex-shrink-0 h-16px border-bottom d-flex align-items-center gap-3 justify-content-between">
                      <span class="d-flex align-items-center gap-1">
                        <span class="w-8px h-8px bg-danger rounded-pill"></span>
                        <span class="w-8px h-8px bg-success rounded-pill"></span>
                        <span class="w-8px h-8px bg-warning rounded-pill"></span>
                      </span>
                      <span class="w-8px h-8px bg-light rounded-pill"></span>
                    </span>
                    <span class="h-100 flex-grow-1 d-flex flex-column justify-content-between gap-1">
                      <span class="p-2">
                        <span class="w-25 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-50 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-100 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-75 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-25 d-block bg-light rounded-1 h-6px mb-1"></span>
                      </span>
                      <span class="w-100 bg-light h-6px ms-1"></span>
                    </span>
                  </span>
                </span>
              </span>
              <span class="d-block shadow-none fs-12 fw-semibold text-center pt-2">Vertical</span>
            </label>
          </div>
          <div class="col-12 col-sm-4">
            <!-- HORIZONTAL -->
            <input class="form-check-input d-none" data-attribute="data-main-layout" name="layoutsModes" value="horizontal" type="radio" id="horizontalLayouts">
            <label for="horizontalLayouts" class="switcher-card w-100">
              <span class="border d-block rounded h-100px overflow-hidden">
                <span class="d-flex h-100">
                  <span class="d-flex flex-column flex-grow-1">
                    <span class="px-2 flex-shrink-0 h-16px border-bottom d-flex align-items-center gap-3 justify-content-between">
                      <span class="d-flex align-items-center gap-1">
                        <span class="w-8px h-8px bg-danger rounded-pill"></span>
                        <span class="w-8px h-8px bg-success rounded-pill"></span>
                        <span class="w-8px h-8px bg-warning rounded-pill"></span>
                      </span>
                      <span class="w-8px h-8px bg-light rounded-pill"></span>
                    </span>
                    <span class="d-flex h-16px flex-shrink-0 border-end">
                      <span class="w-20px h-16px bg-light d-block"></span>
                      <span class="w-100 bg-primary-subtle d-flex justify-content-between p-1">
                        <span class="d-flex gap-2">
                          <span class="w-20px h-6px bg-light rounded d-block mb-1"></span>
                          <span class="w-20px h-6px bg-light rounded d-block mb-1"></span>
                        </span>
                        <span class="w-20px h-6px bg-light rounded d-block mb-1"></span>
                      </span>
                    </span>
                    <span class="h-100 flex-grow-1 d-flex flex-column justify-content-between gap-1">
                      <span class="p-2">
                        <span class="w-25 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-50 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-100 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-75 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-25 d-block bg-light rounded-1 h-6px mb-1"></span>
                      </span>
                      <span class="w-100 bg-light h-6px"></span>
                    </span>
                  </span>
                </span>
              </span>
              <span class="d-block shadow-none fs-12 fw-semibold text-center pt-2">Horizontal</span>
            </label>
          </div>
          <div class="col-12 col-sm-4">
            <!-- 2 COLUMN -->
            <input class="form-check-input d-none" data-attribute="data-main-layout" name="layoutsModes" value="two-column" type="radio" id="2ColumnLayouts">
            <label for="2ColumnLayouts" class="switcher-card w-100">
              <span class="border d-block rounded h-100px overflow-hidden">
                <span class="d-flex h-100">
                  <span class="w-16px d-flex flex-column h-100 flex-shrink-0 border-end">
                    <span class="h-16px flex-shrink-0 bg-light d-block"></span>
                    <span class="h-100 bg-light d-flex flex-column justify-content-between p-1">
                      <span>
                        <span class="h-6px bg-primary-subtle rounded d-block mb-1"></span>
                        <span class="h-6px bg-primary-subtle rounded d-block mb-1"></span>
                      </span>
                      <span class="h-6px bg-primary-subtle rounded d-block mb-1"></span>
                    </span>
                  </span>
                  <span class="w-30px ms-1 d-flex flex-column h-100 flex-shrink-0 border-end">
                    <span class="h-16px flex-shrink-0 bg-light d-block"></span>
                    <span class="h-100 flex-grow-1 bg-primary-subtle d-flex flex-column justify-content-between p-1">
                      <span>
                        <span class="h-6px bg-light rounded d-block mb-1"></span>
                        <span class="h-6px bg-light rounded d-block mb-1"></span>
                      </span>
                      <span class="h-6px bg-light rounded d-block mb-1"></span>
                    </span>
                  </span>
                  <span class="d-flex flex-column flex-grow-1">
                    <span class="px-2 flex-shrink-0 h-16px border-bottom d-flex align-items-center gap-3 justify-content-between">
                      <span class="d-flex align-items-center gap-1">
                        <span class="w-8px h-8px bg-danger rounded-pill"></span>
                        <span class="w-8px h-8px bg-success rounded-pill"></span>
                        <span class="w-8px h-8px bg-warning rounded-pill"></span>
                      </span>
                      <span class="w-8px h-8px bg-light rounded-pill"></span>
                    </span>
                    <span class="h-100 flex-grow-1 d-flex flex-column justify-content-between gap-1">
                      <span class="p-2">
                        <span class="w-25 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-50 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-100 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-75 d-block bg-light rounded-1 h-6px mb-1"></span>
                        <span class="w-25 d-block bg-light rounded-1 h-6px mb-1"></span>
                      </span>
                      <span class="w-100 bg-light h-6px ms-1"></span>
                    </span>
                  </span>
                </span>
              </span>
              <span class="d-block shadow-none fs-12 fw-semibold text-center pt-2">Dos Columnas</span>
            </label>
          </div>
          <div class="col-12 col-sm-4">
            

      <!-- FONT  -->
      <h6 class="mb-2 fs-5">Tipo de letra</h6>
      <p class="text-muted">Cambie el tipo de letra.</p>
      <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6">
          <!-- FONT_BODY -->
          <label class="form-label text-muted fw-semibold fs-12 d-block" for="body-font-choice">Estilo de cuerpo</label>
          <select class="form-select" id="body-font-choice" data-attribute="data-font-body">
            <option value="Inter">Inter</option>
            <option value="Poppins">Poppins</option>
            <option value="Roboto">Roboto</option>
            <option value="Open Sans">Open Sans</option>
            <option value="Lato">Lato</option>
          </select>
        </div>
        <div class="col-12 col-sm-6">
          <!-- FONT_HEADING -->
          <label class="form-label text-muted fw-semibold fs-12 d-block" for="heading-font-choice">Estilo de cabecera</label>
          <select class="form-select" id="heading-font-choice" data-attribute="data-font-heading">
            <option value="Inter">Inter</option>
            <option value="Poppins">Poppins</option>
            <option value="Roboto">Roboto</option>
            <option value="Open Sans">Open Sans</option>
            <option value="Lato">Lato</option>
          </select>
        </div>
      </div>

      <!-- FONT_SIZE -->
      <h6 class="mb-2 fs-5">Tamaño de texto</h6>
      <p class="text-muted">Cambie el tamaño del texto.</p>

      <div class="list-group flex-row gap-3 mb-3 template-customizer mb-5">
        <!-- SM -->
        <label class="list-group-item form-check rounded mb-0">
          <span class="d-flex flex-fill my-1">
            <span class="form-check-label d-flex">
              <span class="fw-semibold">Pequeño</span>
            </span>
            <input type="radio" data-attribute="data-font-size" class="form-check-input cursor-pointer ms-auto" name="font-size-options" value="sm">
          </span>
        </label>
        <!-- MD -->
        <label class="list-group-item form-check rounded mb-0">
          <span class="d-flex flex-fill my-1">
            <span class="form-check-label d-flex">
              <span class="fw-semibold">Mediano</span>
            </span>
            <input type="radio" data-attribute="data-font-size" class="form-check-input cursor-pointer ms-auto" name="font-size-options" value="md">
          </span>
        </label>
        <!-- LG -->
        <label class="list-group-item form-check rounded mb-0">
          <span class="d-flex flex-fill my-1">
            <span class="form-check-label d-flex">
              <span class="fw-semibold">Grande</span>
            </span>
            <input type="radio" data-attribute="data-font-size" class="form-check-input cursor-pointer ms-auto" name="font-size-options" value="lg">
          </span>
        </label>
      </div>

      <!-- LAYOUT_ROUNDED -->
      <h6 class="mb-2 fs-5">Bordes redondos</h6>
      <p class="text-muted">Asigne el nivel de bordes de las cajas de texto.</p>

      <div class="list-group flex-row flex-wrap flex-sm-nowrap gap-3 mb-3 template-customizer mb-5">
        <!-- MD -->
        <label class="list-group-item form-check rounded mb-0">
          <span class="d-flex flex-fill my-1">
            <span class="form-check-label d-flex">
              <span class="fw-semibold">Level 1</span>
            </span>
            <input type="radio" data-attribute="data-layout-rounded" class="form-check-input cursor-pointer ms-auto" name="rounded-options" value="md">
          </span>
        </label>
        <!-- LG -->
        <label class="list-group-item form-check rounded mb-0">
          <span class="d-flex flex-fill my-1">
            <span class="form-check-label d-flex">
              <span class="fw-semibold">Level 2</span>
            </span>
            <input type="radio" data-attribute="data-layout-rounded" class="form-check-input cursor-pointer ms-auto" name="rounded-options" value="lg">
          </span>
        </label>
        <!-- XL -->
        <label class="list-group-item form-check rounded mb-0">
          <span class="d-flex flex-fill my-1">
            <span class="form-check-label d-flex">
              <span class="fw-semibold">Level 3</span>
            </span>
            <input type="radio" data-attribute="data-layout-rounded" class="form-check-input cursor-pointer ms-auto" name="rounded-options" value="xl">
          </span>
        </label>
      </div>
    <div class="offcanvas-header border-top hstack gap-3 justify-content-center">
      <button type="button" id="resetSettings" class="btn btn-dark">Restablecer configuración</button>

    </div>
  </div>
</div>
<!-- END SWITCHER -->
