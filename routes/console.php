<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Команда для обнавления метрик
// php artisan metrics:update — запускает команду вручную
// Artisan::command('metrics:update', function () {
//     Artisan::call(\App\Console\Commands\UpdateMetrics::class);
// })->purpose('Обновляет метрики сайтов')->hourly();
