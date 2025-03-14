<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexMetrikaService
{
    protected string $apiUrl = 'https://api-metrika.yandex.net/stat/v1/data';
    protected string $token;
    protected string $counterId;

    // public function __construct(string $token, string $counterId)
    // {
    //     $this->token = $token;
    //     $this->counterId = $counterId;
    // }

    public function __construct()
    {
        $this->token = config('services.yandex_metrika.token');
        $this->counterId = config('services.yandex_metrika.counter_id');
    }

    /**
     * Получает данные о посещаемости за заданный период
     *
     * @param string $dateFrom — Начальная дата (формат YYYY-MM-DD)
     * @param string $dateTo — Конечная дата (формат YYYY-MM-DD)
     * @return array|null
     */
    public function getVisits(string $dateFrom, string $dateTo): ?array
    {
        // Закомментирован код, чтобы избежать ошибки
        $response = Http::withHeaders([
            'Authorization' => 'OAuth ' . $this->token,
        ])->get($this->apiUrl, [
            'ids' => $this->counterId,
            'metrics' => 'ym:s:visits', // Метрика: визиты
            'date1' => $dateFrom,
            'date2' => $dateTo,
            'accuracy' => 'full',
        ]);

        // Заглушка возвращает пустой массив
        // return [];

        // Если вы хотите возвращать ошибку в логах, оставьте эту часть:
        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Ошибка при запросе данных из Яндекс Метрики', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }
}
