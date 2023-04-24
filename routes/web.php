<?php

use FluxErp\Http\Controllers\PresentationController;
use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\PrintDataController;
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

Route::get('print/public/{uuid}', [PrintDataController::class, 'showHtmlPublic'])->name('print.public-html-show');
Route::get('presentation/public/{uuid}', [PresentationController::class, 'showHtmlPublic'])
    ->name('presentation.public-html-show');
Route::any('print/{view}/{model}/{id}/{asPdf?}', [PrintController::class, 'render'])
    ->where('model', '(.*)')
    ->name('print.render');
