<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use App\Models\Metrics;
use Illuminate\Support\Facades\Log;

class UpdateMetrics extends Command
{
    protected $signature = 'metrics:update';
    protected $description = 'Обновляет метрики сайтов из Яндекс.Метрики и Яндекс.Партнёрки';

    public function handle()
    {
        $this->info('Начало обновления метрик...');
        Log::info('Начало обновления метрик...');

        // Получаем все сайты
        $sites = Site::all();

        // Проходим по каждому сайту
        foreach ($sites as $site) {
            // Логируем запрос к Яндекс.Метрике и Яндекс.Партнёрке
            $this->info("Запрос к Яндекс.Метрике и Яндекс.Партнёрке для сайта: {$site->title}");
            Log::info("Запрос к Яндекс.Метрике и Яндекс.Партнёрке для сайта: {$site->title}");

            try {
                // Получаем метрики из Яндекс.Метрики
                $metrikaMetrics = $site->getMetricsFromYandexMetrika();

                // Получаем доход из Яндекс.Партнёрки
                $revenue = $site->getRevenueFromYandexPartner();

                // Обновляем или создаем запись метрик за сегодня
                $metrics = Metrics::updateOrCreate(
                    ['site_id' => $site->id, 'date' => today()->toDateString()],
                    [
                        'total_revenue' => $revenue,
                        'unique_visitors' => $metrikaMetrics['unique_visitors'],
                        'page_views' => $metrikaMetrics['page_views'],
                    ]
                );

                // Проверяем, есть ли данные за вчерашний день
                $yesterdayMetrics = Metrics::where('site_id', $site->id)
                    ->whereDate('date', today()->subDay())
                    ->first();

                if (!$yesterdayMetrics) {
                    // Если данных за вчера нет, создаем запись с нулевыми значениями
                    Metrics::create([
                        'site_id' => $site->id,
                        'date' => today()->subDay(),
                        'total_revenue' => 0,
                        'unique_visitors' => 0,
                        'page_views' => 0,
                    ]);
                }

                $this->info("Метрики обновлены для сайта: {$site->title}");
                Log::info("Метрики обновлены для сайта: {$site->title}");

            } catch (\Exception $e) {
                Log::error("Ошибка при получении метрик для сайта {$site->title}: {$e->getMessage()}");
                $this->error("Ошибка при получении метрик для сайта {$site->title}: {$e->getMessage()}");
            }
        }

        $this->info('Обновление метрик завершено.');
        Log::info('Обновление метрик завершено.');
    }
}
