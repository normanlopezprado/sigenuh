<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;

Route::middleware('auth')
    ->get('/calendar/month', [CalendarController::class, 'monthData']);
