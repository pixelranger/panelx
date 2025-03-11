<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\TestYandexPartnerController;
use App\Http\Controllers\YandexPartnerController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/test-yandex-partner', [TestYandexPartnerController::class, 'test']);

Route::get('/yandex-partner', [YandexPartnerController::class, 'showRevenue']);
