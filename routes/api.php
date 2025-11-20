<?php

use App\Http\Controllers\Api\SiteStatusController;
use Illuminate\Support\Facades\Route;

Route::post('/site-status', SiteStatusController::class)->name('api.site-status');
