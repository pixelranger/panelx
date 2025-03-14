<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\Site;
// use App\Models\SiteMetric;

// class UpdateMetrics extends Command
// {
//     protected $signature = 'metrics:update';
//     protected $description = 'Обновление метрик сайтов из API Яндекса';

//     public function handle()
//     {
//         foreach (Site::all() as $site) {
//             $metrics = $this->fetchMetrics($site->url);

//             SiteMetric::updateOrCreate(
//                 ['site_id' => $site->id, 'date' => now()->format('Y-m-d')],
//                 [
//                     'unique_visitors' => $metrics['unique_visitors'],
//                     'page_views' => $metrics['page_views'],
//                     'total_revenue' => $metrics['total_revenue'],
//                 ]
//             );
//         }

//         $this->info('Метрики обновлены.');
//     }

//     private function fetchMetrics($url)
//     {
//         return [
//             'unique_visitors' => rand(100, 500),
//             'page_views' => rand(500, 2000),
//             'total_revenue' => rand(50, 500) / 10,
//         ];
//     }
// }

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Iodev\Whois\Factory;

class UpdateMetrics extends Command
{
    protected $signature = 'metrics:update';
    protected $description = 'Обновляет метрики сайтов и дату окончания регистрации домена';

    public function handle(): void
    {
        $whois = Factory::get()->createWhois();

        Site::all()->each(function ($site) use ($whois) {
            // Обновление метрик
            $token = config('services.yandex.token');
            $counterId = $site->counter_id;

            $response = Http::withHeaders([
                'Authorization' => "OAuth $token",
            ])->get("https://api-metrika.yandex.net/stat/v1/data", [
                'ids' => $counterId,
                'metrics' => 'ym:pv:pageviews,ym:pv:users',
                'date1' => 'today',
                'date2' => 'today',
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $site->unique_visitors = $data[0]['metrics'][1] ?? 0;
                $site->page_views = $data[0]['metrics'][0] ?? 0;
            }

            // Обновление даты окончания регистрации
            $info = $whois->loadDomainInfo(parse_url($site->url, PHP_URL_HOST));
            if ($info) {
                $site->domain_expiration_date = $info->expirationDate->format('Y-m-d');
            }

            $site->save();
        });

        $this->info('Метрики и даты окончания регистрации доменов обновлены.');
    }
}
