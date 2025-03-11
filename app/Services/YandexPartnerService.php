<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class YandexPartnerService
{
    private $client;
    private $oauthToken;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://partner2.yandex.ru/api/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'OAuth ' . config('services.yandex_partner.oauth_token'),
            ],
        ]);

        $this->oauthToken = config('services.yandex_partner.oauth_token');
    }

    /**
     * Получение данных о доходе по сайту за вчера и сегодня
     *
     * @param string $domain Домен сайта (например, oops.ru)
     * @return array
     */
    public function getRevenueData(string $domain): array
    {
        try {
            // Получаем данные за вчера
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $yesterdayData = $this->getRevenueForDate($domain, $yesterday);

            // Получаем данные за сегодня
            $today = date('Y-m-d');
            $todayData = $this->getRevenueForDate($domain, $today);

            return [
                'yesterday' => $yesterdayData,
                'today' => $todayData,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при получении данных из Яндекс.Партнерской программы: ' . $e->getMessage());
            return [
                'yesterday' => 0.0,
                'today' => 0.0,
            ];
        }
    }

    /**
     * Получение данных о доходе за конкретную дату
     *
     * @param string $domain Домен сайта
     * @param string $date Дата в формате YYYY-MM-DD
     * @return float
     */
    private function getRevenueForDate(string $domain, string $date): float
    {
        try {
            $response = $this->client->get("statistics2/get.json", [
                'query' => [
                    'lang' => 'ru',
                    'pretty' => 1,
                    'period' => 'thismonth',
                    'dimension_field' => 'date|day', // Получение данных по дням
                    'entity_field' => 'page_level', // На уровне страниц
                    'field' => 'partner_wo_nds', // Доход партнера без НДС
                    'date' => $date,
                    'domain' => $domain,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            // Логируем полный ответ для отладки
            Log::info('Полный ответ Яндекс.Партнер API:', $data);

            // Проверяем наличие ошибок
            if (isset($data['error_type'])) {
                Log::error('Ошибка в ответе Яндекс.Партнер API: ' . $data['message']);
                return 0.0; // Возвращаем 0, если есть ошибка
            }

            // Извлекаем доход из данных
            $revenue = 0.0;
            if (isset($data['data']['points'])) {
                foreach ($data['data']['points'] as $point) {
                    // Проверяем дату для расчета дохода
                    if ($point['dimensions']['date'][0] == $date) {
                        $revenue = $point['measures'][0]['partner_wo_nds'];
                        break;
                    }
                }
            }

            return $revenue;
        } catch (GuzzleException $e) {
            Log::error('Ошибка при запросе данных из Яндекс.Партнерской программы: ' . $e->getMessage());
            return 0.0; // Возвращаем 0 в случае ошибки
        }
    }
}
