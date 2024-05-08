<?php

use FluxErp\Http\Controllers\AssetController;
use FluxErp\Http\Controllers\LoginLinkController;
use FluxErp\Http\Middleware\NoAuth;
use FluxErp\Livewire\InstallWizard;
use Illuminate\Support\Facades\Route;

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
Route::middleware(NoAuth::class)->get('/install', InstallWizard::class)->name('flux.install');

Route::get('/login-link', LoginLinkController::class)->name('login-link');

Route::middleware('cache.headers:public;max_age=31536000;etag')->group(function () {
    Route::get('/manifest.json', [AssetController::class, 'manifest'])->name('manifest');
    Route::get('favicon.svg', [AssetController::class, 'favicon'])->name('favicon');
    Route::get('/flux-assets/{file}', [AssetController::class, 'asset'])
        ->where('file', '.+')
        ->name('flux-asset');
    Route::get('/pwa-service-worker', [AssetController::class, 'pwaServiceWorker'])
        ->name('pwa-service-worker');
});

Route::get('/mail-pixel/{communication:uuid?}', [AssetController::class, 'mailPixel'])->name('mail-pixel');
