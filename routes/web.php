<?php

use App\Http\Controllers\WordPressSiteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/sites');

Route::resource('sites', WordPressSiteController::class)->except(['show']);

Route::post('sites/{site}/start', [WordPressSiteController::class, 'start'])->name('sites.start');
Route::post('sites/{site}/stop', [WordPressSiteController::class, 'stop'])->name('sites.stop');
