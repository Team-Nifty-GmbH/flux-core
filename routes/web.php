<?php

use FluxErp\Http\Controllers\LoginLinkController;
use FluxErp\Http\Middleware\NoAuth;
use FluxErp\Livewire\InstallWizard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/pwa-service-worker', function () {
    return response(Vite::content('resources/js/sw.js', 'flux/build'))
        ->header('Content-Type', 'application/javascript');
})->name('pwa-service-worker');

Route::middleware(NoAuth::class)->get('/install', InstallWizard::class)->name('flux.install');

Route::get('/login-link', LoginLinkController::class)->name('login-link');
