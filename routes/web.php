<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\UserHospitalController;
use App\Http\Controllers\HospitalFloorController;
use App\Http\Controllers\HospitalFloorServiceController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IngredientController;


Route::get('/', function () {
    return view('welcome');

});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth','permission:dashboard.view'])
    ->name('dashboard');

Route::post('/user/select-hospital/{hospital}', [UserHospitalController::class, 'select'])
    ->middleware(['auth','permission:users.select-hospital'])
    ->name('user.select-hospital');

Route::middleware(['auth'])->group(function () {
    Route::get('servicios',                [ServiceController::class,'index'])->name('servicios.index')->middleware('permission:servicios.index');
    Route::get('servicios/create',         [ServiceController::class,'create'])->name('servicios.create')->middleware('permission:servicios.create');
    Route::post('servicios',               [ServiceController::class,'store'])->name('servicios.store')->middleware('permission:servicios.create');
    Route::get('servicios/{servicio}',     [ServiceController::class,'show'])->name('servicios.show')->middleware('permission:servicios.index');
    Route::get('servicios/{servicio}/edit',[ServiceController::class,'edit'])->name('servicios.edit')->middleware('permission:servicios.edit');
    Route::put('servicios/{servicio}',     [ServiceController::class,'update'])->name('servicios.update')->middleware('permission:servicios.edit');
    Route::delete('servicios/{servicio}',  [ServiceController::class,'destroy'])->name('servicios.destroy')->middleware('permission:servicios.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('hospitales',                 [HospitalController::class,'index'])->name('hospitales.index')->middleware('permission:hospitales.index');
    Route::get('hospitales/create',          [HospitalController::class,'create'])->name('hospitales.create')->middleware('permission:hospitales.create');
    Route::post('hospitales',                [HospitalController::class,'store'])->name('hospitales.store')->middleware('permission:hospitales.create');
    Route::get('hospitales/{hospital}',      [HospitalController::class,'show'])->name('hospitales.show')->middleware('permission:hospitales.index');
    Route::get('hospitales/{hospital}/edit', [HospitalController::class,'edit'])->name('hospitales.edit')->middleware('permission:hospitales.edit');
    Route::put('hospitales/{hospital}',      [HospitalController::class,'update'])->name('hospitales.update')->middleware('permission:hospitales.edit');
    Route::delete('hospitales/{hospital}',   [HospitalController::class,'destroy'])->name('hospitales.destroy')->middleware('permission:hospitales.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('niveles',               [NivelController::class,'index'])->name('niveles.index')->middleware('permission:niveles.index');
    Route::get('niveles/create',        [NivelController::class,'create'])->name('niveles.create')->middleware('permission:niveles.create');
    Route::post('niveles',              [NivelController::class,'store'])->name('niveles.store')->middleware('permission:niveles.create');
    Route::get('niveles/{nivel}',       [NivelController::class,'show'])->name('niveles.show')->middleware('permission:niveles.index');
    Route::get('niveles/{nivel}/edit',  [NivelController::class,'edit'])->name('niveles.edit')->middleware('permission:niveles.edit');
    Route::put('niveles/{nivel}',       [NivelController::class,'update'])->name('niveles.update')->middleware('permission:niveles.edit');
    Route::delete('niveles/{nivel}',    [NivelController::class,'destroy'])->name('niveles.destroy')->middleware('permission:niveles.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('hospital-floors',  [HospitalFloorController::class, 'edit'])->name('hospital-floors.edit')->middleware('permission:hospitalfloors.edit');
    Route::post('hospital-floors', [HospitalFloorController::class, 'update'])->name('hospital-floors.update')->middleware('permission:hospitalfloors.update');
});


Route::middleware('auth')->group(function () {
    Route::get('/hospital-floor-services',  [HospitalFloorServiceController::class, 'edit'])
        ->name('hospital-floor-services.edit');
    Route::post('/hospital-floor-services', [HospitalFloorServiceController::class, 'update'])
        ->name('hospital-floor-services.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('beds',             [BedController::class,'index'])->name('beds.index')->middleware('permission:beds.index');
    Route::get('beds/create',      [BedController::class,'create'])->name('beds.create')->middleware('permission:beds.create');
    Route::post('beds',            [BedController::class,'store'])->name('beds.store')->middleware('permission:beds.create');
    Route::get('beds/{bed}',       [BedController::class,'show'])->name('beds.show')->middleware('permission:beds.index');
    Route::get('beds/{bed}/edit',  [BedController::class,'edit'])->name('beds.edit')->middleware('permission:beds.edit');
    Route::put('beds/{bed}',       [BedController::class,'update'])->name('beds.update')->middleware('permission:beds.edit');
    Route::delete('beds/{bed}',    [BedController::class,'destroy'])->name('beds.destroy')->middleware('permission:beds.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('ingredients',                 [IngredientController::class,'index'])->name('ingredients.index')->middleware('permission:ingredients.index');
    Route::get('ingredients/create',          [IngredientController::class,'create'])->name('ingredients.create')->middleware('permission:ingredients.create');
    Route::post('ingredients',                [IngredientController::class,'store'])->name('ingredients.store')->middleware('permission:ingredients.create');
    Route::get('ingredients/{ingredient}',    [IngredientController::class,'show'])->name('ingredients.show')->middleware('permission:ingredients.index');
    Route::get('ingredients/{ingredient}/edit',[IngredientController::class,'edit'])->name('ingredients.edit')->middleware('permission:ingredients.edit');
    Route::put('ingredients/{ingredient}',    [IngredientController::class,'update'])->name('ingredients.update')->middleware('permission:ingredients.edit');
    Route::delete('ingredients/{ingredient}', [IngredientController::class,'destroy'])->name('ingredients.destroy')->middleware('permission:ingredients.delete');
});


Route::middleware(['auth'])->group(function () {
    Route::get('usuarios',                [UserController::class,'index'])->name('usuarios.index')->middleware('permission:users.index');
    Route::get('usuarios/create',         [UserController::class,'create'])->name('usuarios.create')->middleware('permission:users.create');
    Route::post('usuarios',               [UserController::class,'store'])->name('usuarios.store')->middleware('permission:users.create');
    Route::get('usuarios/{usuario}',      [UserController::class,'show'])->name('usuarios.show')->middleware('permission:users.index');
    Route::get('usuarios/{usuario}/edit', [UserController::class,'edit'])->name('usuarios.edit')->middleware('permission:users.edit');
    Route::put('usuarios/{usuario}',      [UserController::class,'update'])->name('usuarios.update')->middleware('permission:users.edit');
    Route::delete('usuarios/{usuario}',   [UserController::class,'destroy'])->name('usuarios.destroy')->middleware('permission:users.delete');
});


