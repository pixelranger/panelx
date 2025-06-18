<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Metrics;
use Illuminate\Support\Facades\Http;
use Iodev\Whois\Factory as Whois;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Services\YandexMetrikaService;
use Illuminate\Support\Facades\Auth;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'url',
        'url_admin',
        'income_forecast',
        'domain_expiration_date',
        'created_by',
    ];

    // Аксессор для фавиконки
    public function getFaviconAttribute(): string
    {
        return "<img src='{$this->url}/favicon.png' width='32' height='32' />";
    }

    // Аксессор для уникальных посетителей
    public function getUniqueVisitorsAttribute(): int
    {
        return $this->metricsToday()?->unique_visitors ?? 0;
    }

    // Аксессор для просмотров страниц
    public function getPageViewsAttribute(): int
    {
        return $this->metricsToday()?->page_views ?? 0;
    }

    // Аксессор для дохода
    public function getTotalRevenueAttribute(): float
    {
        return $this->metricsToday()?->total_revenue ?? 0;
    }

    // Предполагаемый метод для получения метрик за сегодня
    public function metricsToday()
    {
        return $this->hasOne(Metrics::class)->whereDate('created_at', today())->first();
    }

    // Устанавливаем значение created_by автоматически
    public static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            $site->created_by = Auth::user()->id ?? 1;  // Если авторизованный пользователь отсутствует, ставим 1 (администратор по умолчанию)
        });

        static::created(function ($site) {
            // Создание записи метрик для нового сайта
            Metrics::create([
                'site_id' => $site->id,
                'unique_visitors' => 0,  // Установите начальное значение
                'page_views' => 0,       // Установите начальное значение
                'total_revenue' => 0.0,  // Установите начальное значение
                'created_at' => now(),
                'updated_at' => now(),
                'date' => now()->toDateString(),
            ]);
        });
    }

    /**
     * DEPRECATED.
     * TODO: NEED DELETED
     * Метод для обновления метрик сайта
     */
    // public function updateMetrics()
    // {
    //     // Получаем доход из Яндекс.Партнёрки
    //     $revenue = $this->getRevenueFromYandexPartner();

    //     // Получаем метрики из Яндекс.Метрики
    //     $metrikaMetrics = $this->getMetricsFromYandexMetrika();

    //     // Обновляем или создаем запись метрик
    //     $metrics = Metrics::updateOrCreate(
    //         ['site_id' => $this->id, 'date' => today()->toDateString()],
    //         [
    //             'total_revenue' => $revenue,
    //             'unique_visitors' => $metrikaMetrics['unique_visitors'],
    //             'page_views' => $metrikaMetrics['page_views'],
    //         ]
    //     );

    //     return $metrics;
    // }




    // Получение дохода из Яндекс.Партнёрки
    public function getRevenueFromYandexPartner(): float
    {
        $client = new Client([
            'base_uri' => 'https://partner2.yandex.ru/api/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'OAuth ' . config('services.yandex_partner.oauth_token'),
            ],
        ]);

        try {
            $query = [
                'lang' => 'ru',
                'pretty' => 1,
                'period' => 'today', // Данные за сегодня
                'dimension_field' => 'date|day',
                'entity_field' => 'page_level',
                'field' => 'partner_wo_nds', // Доход партнера без НДС
            ];

            Log::info('Запрос к Яндекс.Партнер API:', $query);

            $response = $client->get("statistics2/get.json", [
                'query' => $query,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Логируем полный ответ для отладки
            Log::info('Ответ Яндекс.Партнер API:', $data);

            if (isset($data['error_type'])) {
                Log::error('Ошибка в ответе Яндекс.Партнер API: ' . $data['message']);
                return 0.0;
            }

            $revenue = 0.0;
            if (isset($data['data']['points'])) {
                foreach ($data['data']['points'] as $point) {
                    if ($point['dimensions']['date'][0] == today()->toDateString()) {
                        $revenue = $point['measures'][0]['partner_wo_nds'];
                        break;
                    }
                }
            }

            Log::info('Полученный доход: ' . $revenue);

            return $revenue;
        } catch (GuzzleException $e) {
            Log::error('Ошибка при запросе данных из Яндекс.Партнёрки: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getMetricsFromYandexMetrika($token): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "OAuth {$token}",
            ])->get('https://api-metrika.yandex.net/stat/v1/data', [
                'ids' => $this->counter_id,
                'metrics' => 'ym:s:visits,ym:s:pageviews', // Уникальные посетители и просмотры страниц
                'date1' => today()->toDateString(), // Данные за сегодня
                'date2' => today()->toDateString(),
            ]);

            $data = $response->json();

            Log::info('Ответ Яндекс.Метрики:', $data);

            if (isset($data['errors'])) {
                Log::error('Ошибка в ответе Яндекс.Метрики: ' . json_encode($data['errors']));
                return ['unique_visitors' => 0, 'page_views' => 0];
            }

            // Извлекаем данные
            $uniqueVisitors = $data['data'][0]['metrics'][0] ?? 0;
            $pageViews = $data['data'][0]['metrics'][1] ?? 0;

            return [
                'unique_visitors' => $uniqueVisitors,
                'page_views' => $pageViews,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при запросе данных из Яндекс.Метрики: ' . $e->getMessage());
            return ['unique_visitors' => 0, 'page_views' => 0];
        }
    }

    public function getFormattedTotalRevenueAttribute(): string
    {
        return $this->total_revenue !== null ? (string) $this->total_revenue : 'Нет данных';
    }


    // Аксессор для уникальных посетителей с разницей
    public function getUniqueVisitorsWithDifferenceAttribute(): string
    {
        $today = $this->metricsToday();
        $yesterday = $this->metricsYesterday();

        $todayValue = $today ? $today->unique_visitors : 0;
        $yesterdayValue = $yesterday ? $yesterday->unique_visitors : 0;
        $difference = $todayValue - $yesterdayValue;

        $color = $difference >= 0 ? 'green' : 'red';
        return "{$todayValue} <span style='color: {$color};'>(" . ($difference >= 0 ? '+' : '') . "{$difference})</span>";
    }

    // Аксессор для просмотров страниц с разницей
    public function getPageViewsWithDifferenceAttribute(): string
    {
        $today = $this->metricsToday();
        $yesterday = $this->metricsYesterday();

        $todayValue = $today ? $today->page_views : 0;
        $yesterdayValue = $yesterday ? $yesterday->page_views : 0;
        $difference = $todayValue - $yesterdayValue;

        $color = $difference >= 0 ? 'green' : 'red';
        return "{$todayValue} <span style='color: {$color};'>(" . ($difference >= 0 ? '+' : '') . "{$difference})</span>";
    }

    // Аксессор для дохода с разницей
    public function getFormattedTotalRevenueWithDifferenceAttribute(): string
    {
        $today = $this->metricsToday();
        $yesterday = $this->metricsYesterday();

        $todayValue = $today ? $today->total_revenue : 0;
        $yesterdayValue = $yesterday ? $yesterday->total_revenue : 0;
        $difference = $todayValue - $yesterdayValue;

        $color = $difference >= 0 ? 'green' : 'red';
        return "{$this->formatted_total_revenue} <span style='color: {$color};'>(" . ($difference >= 0 ? '+' : '') . "{$difference})</span>";
    }


    // Метод для получения метрик за вчерашний день
    public function metricsYesterday()
    {
        return $this->hasOne(Metrics::class)->whereDate('created_at', today()->subDay())->first();
    }

    // Аксессор для подсчёта дней до окончания регистрации
    public function getDaysUntilExpirationAttribute(): string
{
    // Если дата окончания регистрации не установлена, возвращаем "Нет данных"
    if (!$this->domain_expiration_date) {
        return 'Нет данных';
    }

    // Получаем текущую дату и дату окончания регистрации
    $today = now();
    $expirationDate = \Carbon\Carbon::parse($this->domain_expiration_date);

    // Вычисляем разницу в днях и приводим к целому числу
    $daysDifference = (int)$today->diffInDays($expirationDate, false);

    // Определяем цвет в зависимости от количества дней
    if ($daysDifference < 0) {
        // Если срок истёк (дни в минусе)
        $color = 'red';
    } elseif ($daysDifference <= 30) {
        // Если осталось 30 дней или меньше
        $color = 'orange';
    } else {
        // Если срок ещё не истёк
        $color = 'green';
    }

    // Возвращаем результат с цветом
    return "<span style='color: {$color};'>{$daysDifference}</span>";
}

}
