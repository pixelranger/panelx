<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\SiteMetric;
use App\Services\YandexPartnerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateSiteMetrics extends Command
{
    protected $signature = 'metrics:update';
    protected $description = 'Обновляет метрики сайтов из API Яндекс.Метрики и партнерской программы';

    public function handle(YandexPartnerService $yandexService)
    {
        $date = now()->toDateString();
        $sites = Site::all();

        foreach ($sites as $site) {
            $metrics = $this->getMetricsFromYandex($site->domain);
            $revenue = $yandexService->getRevenueForDate($site->domain, $date);

            SiteMetric::updateOrCreate(
                ['site_id' => $site->id, 'date' => $date],
                [
                    'unique_visitors' => $metrics['unique_visitors'] ?? 0,
                    'page_views' => $metrics['page_views'] ?? 0,
                    'total_revenue' => $revenue ?? 0.00,
                ]
            );

            $this->info("Обновлены данные для {$site->domain} за {$date}");
        }

        return Command::SUCCESS;
    }

    private function getMetricsFromYandex(string $domain): array
    {
        $token = config('services.yandex_metrika.oauth_token');
        $counterId = config('services.yandex_metrika.counter_id');

        $response = Http::withHeaders([
            'Authorization' => "OAuth {$token}",
        ])->get("https://api-metrika.yandex.net/stat/v1/data", [
            'ids' => $counterId,
            'metrics' => 'ym:s:visits,ym:s:pageviews',
            'date1' => 'today',
            'date2' => 'today',
            'filters' => "ym:s:domainName=='{$domain}'"
        ]);

        if ($response->failed()) {
            Log::error("Ошибка получения метрик для {$domain}: " . $response->body());
            return ['unique_visitors' => 0, 'page_views' => 0];
        }

        $data = $response->json();
        return [
            'unique_visitors' => $data['data'][0]['metrics'][0] ?? 0,
            'page_views' => $data['data'][0]['metrics'][1] ?? 0,
        ];
    }
}

