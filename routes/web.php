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
use App\Http\Controllers\MenuController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout');

Route::resource('servicios', ServiceController::class)
    ->middleware(['auth']);


Route::resource('hospitales', HospitalController::class)
    ->parameters(['hospitales' => 'hospital'])
    ->middleware(['auth']);

Route::resource('niveles', NivelController::class)
    ->parameters(['niveles' => 'nivel'])
    ->middleware(['auth']);


Route::post('/user/select-hospital/{hospital}', [UserHospitalController::class, 'select'])
    ->middleware('auth')
    ->name('user.select-hospital');

Route::middleware(['auth'])->group(function () {
    Route::get('hospital-floors', [HospitalFloorController::class, 'edit'])->name('hospital-floors.edit');
    Route::post('hospital-floors', [HospitalFloorController::class, 'update'])->name('hospital-floors.update');

});

Route::middleware('auth')->group(function () {
    Route::get('/hospital-floor-services',  [HospitalFloorServiceController::class, 'edit'])
        ->name('hospital-floor-services.edit');
    Route::post('/hospital-floor-services', [HospitalFloorServiceController::class, 'update'])
        ->name('hospital-floor-services.update');
});

Route::resource('beds', BedController::class)
    ->middleware('auth');

Route::resource('usuarios', UserController::class)
    ->parameters(['usuarios' => 'usuario'])
    ->middleware(['auth']);

Route::resource('ingredients', IngredientController::class)
    ->middleware('auth');

Route::resource('menus', MenuController::class)
    ->middleware('auth');
