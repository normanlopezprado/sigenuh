<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\HospitalController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\UserHospitalController;
use App\Http\Controllers\HospitalFloorController;
use App\Http\Controllers\HospitalFloorServiceController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffBeneficiaryController;
use App\Http\Controllers\StaffMealController;
use App\Http\Controllers\StaffMealReportController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartRouteController;
use App\Http\Controllers\CollectController;
use App\Http\Controllers\DashboardCartsController;

use App\Http\Controllers\CollectCardsController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Landing
Route::get('/', fn () => view('welcome'));

// ---------------------------
// Recuperación de contraseña
// ---------------------------
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:6,1')->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// ---------------------------
// Verificación de email
// ---------------------------
Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('dashboard')->with('success','Email verificado.');
})->middleware(['auth','signed','throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success','Te enviamos un nuevo enlace de verificación.');
})->middleware(['auth','throttle:6,1'])->name('verification.send');

// ---------------------------
// Autenticación
// ---------------------------
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ---------------------------
// Dashboard
// ---------------------------
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth','verified','permission:dashboard.view'])
    ->name('dashboard');

// ---------------------------
// Selección de hospital
// ---------------------------
Route::post('/user/select-hospital/{hospital}', [UserHospitalController::class, 'select'])
    ->middleware(['auth'])
    ->name('user.select-hospital');

// ---------------------------
// Servicios
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('servicios',                [ServiceController::class,'index'])->name('servicios.index')->middleware('permission:servicios.index');
    Route::get('servicios/create',         [ServiceController::class,'create'])->name('servicios.create')->middleware('permission:servicios.create');
    Route::post('servicios',               [ServiceController::class,'store'])->name('servicios.store')->middleware('permission:servicios.create');
    Route::get('servicios/{servicio}',     [ServiceController::class,'show'])->name('servicios.show')->middleware('permission:servicios.show');
    Route::get('servicios/{servicio}/edit',[ServiceController::class,'edit'])->name('servicios.edit')->middleware('permission:servicios.edit');
    Route::put('servicios/{servicio}',     [ServiceController::class,'update'])->name('servicios.update')->middleware('permission:servicios.edit');
    Route::delete('servicios/{servicio}',  [ServiceController::class,'destroy'])->name('servicios.destroy')->middleware('permission:servicios.delete');
});

// ---------------------------
// Hospitales
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('hospitales',                 [HospitalController::class,'index'])->name('hospitales.index')->middleware('permission:hospitales.index');
    Route::get('hospitales/create',          [HospitalController::class,'create'])->name('hospitales.create')->middleware('permission:hospitales.create');
    Route::post('hospitales',                [HospitalController::class,'store'])->name('hospitales.store')->middleware('permission:hospitales.create');
    Route::get('hospitales/{hospital}',      [HospitalController::class,'show'])->name('hospitales.show')->middleware('permission:hospitales.index');
    Route::get('hospitales/{hospital}/edit', [HospitalController::class,'edit'])->name('hospitales.edit')->middleware('permission:hospitales.edit');
    Route::put('hospitales/{hospital}',      [HospitalController::class,'update'])->name('hospitales.update')->middleware('permission:hospitales.edit');
    Route::delete('hospitales/{hospital}',   [HospitalController::class,'destroy'])->name('hospitales.destroy')->middleware('permission:hospitales.delete');
});

// ---------------------------
// Niveles
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('niveles',               [NivelController::class,'index'])->name('niveles.index')->middleware('permission:niveles.index');
    Route::get('niveles/create',        [NivelController::class,'create'])->name('niveles.create')->middleware('permission:niveles.create');
    Route::post('niveles',              [NivelController::class,'store'])->name('niveles.store')->middleware('permission:niveles.create');
    Route::get('niveles/{nivel}',       [NivelController::class,'show'])->name('niveles.show')->middleware('permission:niveles.index');
    Route::get('niveles/{nivel}/edit',  [NivelController::class,'edit'])->name('niveles.edit')->middleware('permission:niveles.edit');
    Route::put('niveles/{nivel}',       [NivelController::class,'update'])->name('niveles.update')->middleware('permission:niveles.edit');
    Route::delete('niveles/{nivel}',    [NivelController::class,'destroy'])->name('niveles.destroy')->middleware('permission:niveles.delete');
});

// ---------------------------
// Plantas del hospital
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('hospital-floors',  [HospitalFloorController::class, 'edit'])->name('hospital-floors.edit')->middleware('permission:hospitalfloors.edit');
    Route::post('hospital-floors', [HospitalFloorController::class, 'update'])->name('hospital-floors.update')->middleware('permission:hospitalfloors.update');
});

// ---------------------------
// Hospital Floor Services (asignación servicios a plantas)
// ---------------------------
Route::middleware('auth')->group(function () {
    Route::get('/hospital-floor-services',  [HospitalFloorServiceController::class, 'edit'])->name('hospital-floor-services.edit')->middleware('permission:hospital-floor-services.edit');
    Route::post('/hospital-floor-services', [HospitalFloorServiceController::class, 'update'])->name('hospital-floor-services.update')->middleware('permission:hospital-floor-services.update');
});

// ---------------------------
// Camas
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('beds',             [BedController::class,'index'])->name('beds.index')->middleware('permission:beds.index');
    Route::get('beds/create',      [BedController::class,'create'])->name('beds.create')->middleware('permission:beds.create');
    Route::post('beds',            [BedController::class,'store'])->name('beds.store')->middleware('permission:beds.create');
    Route::get('beds/{bed}',       [BedController::class,'show'])->name('beds.show')->middleware('permission:beds.index');
    Route::get('beds/{bed}/edit',  [BedController::class,'edit'])->name('beds.edit')->middleware('permission:beds.edit');
    Route::put('beds/{bed}',       [BedController::class,'update'])->name('beds.update')->middleware('permission:beds.edit');
    Route::delete('beds/{bed}',    [BedController::class,'destroy'])->name('beds.destroy')->middleware('permission:beds.delete');
});

// ---------------------------
// Ingredientes
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('ingredients',                  [IngredientController::class,'index'])->name('ingredients.index')->middleware('permission:ingredients.index');
    Route::get('ingredients/create',           [IngredientController::class,'create'])->name('ingredients.create')->middleware('permission:ingredients.create');
    Route::post('ingredients',                 [IngredientController::class,'store'])->name('ingredients.store')->middleware('permission:ingredients.create');
    Route::get('ingredients/{ingredient}',     [IngredientController::class,'show'])->name('ingredients.show')->middleware('permission:ingredients.index');
    Route::get('ingredients/{ingredient}/edit',[IngredientController::class,'edit'])->name('ingredients.edit')->middleware('permission:ingredients.edit');
    Route::put('ingredients/{ingredient}',     [IngredientController::class,'update'])->name('ingredients.update')->middleware('permission:ingredients.edit');
    Route::delete('ingredients/{ingredient}',  [IngredientController::class,'destroy'])->name('ingredients.destroy')->middleware('permission:ingredients.delete');
});

// ---------------------------
// Usuarios
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('usuarios',                [UserController::class,'index'])->name('usuarios.index')->middleware('permission:users.index');
    Route::get('usuarios/create',         [UserController::class,'create'])->name('usuarios.create')->middleware('permission:users.create');
    Route::post('usuarios',               [UserController::class,'store'])->name('usuarios.store')->middleware('permission:users.create');
    Route::get('usuarios/{usuario}',      [UserController::class,'show'])->name('usuarios.show')->middleware('permission:users.index');
    Route::get('usuarios/{usuario}/edit', [UserController::class,'edit'])->name('usuarios.edit')->middleware('permission:users.edit');
    Route::put('usuarios/{usuario}',      [UserController::class,'update'])->name('usuarios.update')->middleware('permission:users.edit');
    Route::delete('usuarios/{usuario}',   [UserController::class,'destroy'])->name('usuarios.destroy')->middleware('permission:users.delete');
});

// ---------------------------
// Menús
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('menus',               [MenuController::class,'index'])->name('menus.index')->middleware('permission:menus.index');
    Route::get('menus/create',        [MenuController::class,'create'])->name('menus.create')->middleware('permission:menus.create');
    Route::post('menus',              [MenuController::class,'store'])->name('menus.store')->middleware('permission:menus.create');
    Route::get('menus/{menu}/edit',   [MenuController::class,'edit'])->name('menus.edit')->middleware('permission:menus.edit');
    Route::put('menus/{menu}',        [MenuController::class,'update'])->name('menus.update')->middleware('permission:menus.edit');
    Route::delete('menus/{menu}',     [MenuController::class,'destroy'])->name('menus.destroy')->middleware('permission:menus.delete');
});

// ---------------------------
// Calendario de Menús
// ---------------------------
// Declaración explícita (evitamos resource para controlar permisos por acción)
Route::middleware(['auth'])->group(function () {
    Route::get('/calendars',              [CalendarController::class, 'index'])->name('calendars.index')->middleware('permission:calendars.index');
    Route::get('/calendars/create',       [CalendarController::class, 'create'])->name('calendars.create')->middleware('permission:calendars.create');
    Route::post('/calendars',             [CalendarController::class, 'store'])->name('calendars.store')->middleware('permission:calendars.create');
    Route::get('/calendars/{calendar}/edit', [CalendarController::class, 'edit'])->name('calendars.edit')->middleware('permission:calendars.edit');
    Route::put('/calendars/{calendar}',   [CalendarController::class, 'update'])->name('calendars.update')->middleware('permission:calendars.edit');
    Route::delete('/calendars/{calendar}',[CalendarController::class, 'destroy'])->name('calendars.destroy')->middleware('permission:calendars.delete');
});
Route::get('/calendar/month', [CalendarController::class, 'monthData'])->name('calendar.month'); // pública o protégela si lo deseas

// ---------------------------
// Beneficiarios (una sola definición, sin duplicados)
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/staff-beneficiaries',                                 [StaffBeneficiaryController::class, 'index'])->name('staff-beneficiaries.index')->middleware('permission:staff-beneficiaries.index');
    Route::get('/staff-beneficiaries/create',                          [StaffBeneficiaryController::class, 'create'])->name('staff-beneficiaries.create')->middleware('permission:staff-beneficiaries.create');
    Route::post('/staff-beneficiaries',                                [StaffBeneficiaryController::class, 'store'])->name('staff-beneficiaries.store')->middleware('permission:staff-beneficiaries.create');
    Route::get('/staff-beneficiaries/{staff_beneficiary}',             [StaffBeneficiaryController::class, 'show'])->name('staff-beneficiaries.show')->middleware('permission:staff-beneficiaries.index');
    Route::get('/staff-beneficiaries/{staff_beneficiary}/edit',        [StaffBeneficiaryController::class, 'edit'])->name('staff-beneficiaries.edit')->middleware('permission:staff-beneficiaries.edit');
    Route::put('/staff-beneficiaries/{staff_beneficiary}',             [StaffBeneficiaryController::class, 'update'])->name('staff-beneficiaries.update')->middleware('permission:staff-beneficiaries.edit');
    Route::delete('/staff-beneficiaries/{staff_beneficiary}',          [StaffBeneficiaryController::class, 'destroy'])->name('staff-beneficiaries.destroy')->middleware('permission:staff-beneficiaries.delete');
    Route::patch('/staff-beneficiaries/{staff_beneficiary}/toggle-status', [StaffBeneficiaryController::class, 'toggleStatus'])->name('staff-beneficiaries.toggle-status')->middleware('permission:staff-beneficiaries.edit');
});

// ---------------------------
// Entregas (Staff Meals)
// ---------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('staff_meals/delivery',                [StaffMealController::class, 'delivery'])->name('staff_meals.delivery')->middleware('permission:staff-meals.view');

    // Opciones / autocompletes
    Route::get('staff_meals/options/diet-types',      [StaffMealController::class, 'dietTypes'])->name('staff_meals.diet-types')->middleware('permission:staff-meals.view');
    Route::get('staff_meals/options/menus-today',     [StaffMealController::class, 'menusToday'])->name('staff_meals.menus-today')->middleware('permission:staff-meals.view');
    Route::get('staff_meals/search-beneficiaries',    [StaffMealController::class, 'searchBeneficiaries'])->name('staff_meals.search-beneficiaries')->middleware('permission:staff-meals.view');

    // Acción de entrega
    Route::post('staff_meals/deliver',                [StaffMealController::class, 'deliver'])->name('staff_meals.deliver')->middleware('permission:staff-meals.deliver');

    // Listado de entregas
    Route::get('staff_meals/list-deliveries',         [StaffMealController::class, 'listDeliveries'])->name('staff_meals.list-deliveries')->middleware('permission:staff-meals.view');
});

// Reportes de entregas
Route::middleware(['auth','permission:staff-meals.report'])->group(function () {
    Route::get('staff_meals/report', [StaffMealReportController::class, 'deliveriesReport'])->name('staff_meals.report');
});

// ---------------------------
// Carritos
// ---------------------------
Route::middleware(['auth','verified'])->prefix('carts')->name('carts.')->group(function () {
    Route::get('/',                        [CartController::class, 'index'])->name('index')->middleware('permission:carts.index');
    Route::get('/create',                  [CartController::class, 'create'])->name('create')->middleware('permission:carts.create');
    Route::post('/',                       [CartController::class, 'store'])->name('store')->middleware('permission:carts.create');
    Route::get('/{cart}/edit',             [CartController::class, 'edit'])->name('edit')->middleware('permission:carts.edit');
    Route::put('/{cart}',                  [CartController::class, 'update'])->name('update')->middleware('permission:carts.edit');
    Route::delete('/{cart}',               [CartController::class, 'destroy'])->name('destroy')->middleware('permission:carts.delete');

    // Ruta del carrito
    Route::get('/{cart}/route',            [CartController::class, 'editRoute'])->name('route.edit')->middleware('permission:carts.routes.edit');
    Route::put('/{cart}/route',            [CartController::class, 'updateRoute'])->name('route.update')->middleware('permission:carts.routes.update');

    // Servicios asociados
    Route::get('/{cart}/services/available', [CartController::class, 'availableServices'])->name('services.available')->middleware('permission:carts.services.view');
    Route::get('/{cart}/services/selected',  [CartController::class, 'selectedServices'])->name('services.selected')->middleware('permission:carts.services.view');
});

// Pantalla global de rutas
Route::middleware(['auth'])->group(function () {
    Route::get('carts/routes',  [CartRouteController::class, 'edit'])->name('carts.routes.index')->middleware('permission:carts.routes.edit');
    Route::post('carts/routes', [CartRouteController::class, 'update'])->name('carts.routes.update')->middleware('permission:carts.routes.update');
});

// ---------------------------
// Recolección (Collects)
// ---------------------------

// Vista de tarjetas (GET)
Route::get('/collects/cards', [CollectCardsController::class, 'cards'])
    ->name('collects.cards'); // tu Blade usa route('collects.cards')

// Guardado en bloque (POST) – tu Blade usa route('collects.bulk')
Route::post('/collects/bulk', [CollectCardsController::class, 'bulk'])
    ->name('collects.bulk');

// Toggle de cama (PATCH) – tu Blade llama a /collects/bed/{id}/toggle
Route::patch('/collects/bed/{bed}/toggle', [CollectCardsController::class, 'toggleBed'])
    ->name('collects.bed.toggle');



    // ---------------------------
// Dashboard - Tarjetas de carritos
// ---------------------------
Route::get('/dashboard/cars', [DashboardCartsController::class, 'index'])
    ->name('dashboard.cars.index');

Route::get('/dashboard/cars/partial', [DashboardCartsController::class, 'partial'])
    ->name('dashboard.cars.partial');