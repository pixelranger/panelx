<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\UpdateMetrics;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Команда для обнавления метрик
// php artisan metrics:update — запускает команду вручную
// Artisan::command('metrics:update', function () {
//     Artisan::call(UpdateMetrics::class);
// })->purpose('Обновляет метрики сайтов')->hourly();
