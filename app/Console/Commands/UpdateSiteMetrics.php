<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\SiteMetric;
use App\Services\YandexPartnerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Iodev\Whois\Factory;

class UpdateSiteMetrics extends Command
{
    protected $signature = 'metrics:update';
    protected $description = 'Обновляет метрики сайтов из API Яндекс.Метрики и партнерской программы';

    public function handle(YandexPartnerService $yandexService)
    {
        $date = now()->toDateString();
        $sites = Site::all();
        $whois = Factory::get()->createWhois();

        foreach ($sites as $site) {
            $metrics = $this->getMetricsFromYandex($site->domain);
            $revenue = $yandexService->getRevenueForDate($site->domain, $date);
            $expirationDate = $this->getDomainExpirationDate($whois, $site->domain);

            SiteMetric::updateOrCreate(
                ['site_id' => $site->id, 'date' => $date],
                [
                    'unique_visitors' => $metrics['unique_visitors'] ?? 0,
                    'page_views' => $metrics['page_views'] ?? 0,
                    'total_revenue' => $revenue ?? 0.00,
                ]
            );

            // Обновляем дату окончания регистрации домена
            $site->update(['domain_expiration_date' => $expirationDate]);

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
        // return [
        //     'unique_visitors' => 0,
        //     'page_views' => 0,
        // ];
    }

    private function getDomainExpirationDate($whois, string $domain): ?string
    {
        try {
            $info = $whois->loadDomainInfo($domain);
            return $info ? $info->expirationDate : null;
        } catch (\Exception $e) {
            Log::error("Ошибка получения WHOIS для {$domain}: " . $e->getMessage());
            return null;
        }
    }
}
