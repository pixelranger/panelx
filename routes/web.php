<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\TestYandexPartnerController;
use App\Http\Controllers\YandexPartnerController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\YandexMetricController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/test-yandex-partner', [TestYandexPartnerController::class, 'test']);

// Route::get('/yandex-partner', [YandexPartnerController::class, 'showRevenue']);

// Route::get('/sites', [SiteController::class, 'index']);
Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');

Route::get('/login-yandex', [YandexMetricController::class, 'redirectToProvider'])->middleware(['moonshine', \MoonShine\Laravel\Http\Middleware\Authenticate::class]);
Route::get('/callback', [YandexMetricController::class, 'handleProviderCallback'])->middleware(['moonshine', \MoonShine\Laravel\Http\Middleware\Authenticate::class]);
// Route::middleware('moonshine.auth')->get('/metrics', [YandexMetricController::class, 'getMetrics']);
