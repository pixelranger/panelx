<?php

namespace App\Services;

use GuzzleHttp\Client;

class YandexMetrikaService
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.yandex_metrika.client_id');
        $this->clientSecret = config('services.yandex_metrika.client_secret');
        $this->redirectUri = config('services.yandex_metrika.redirect_uri');
    }

    public function getAccessToken($code)
    {
        $client = new Client();
        $response = $client->post('https://oauth.yandex.ru/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function getMetrics($accessToken, $counterId, $startDate, $endDate)
    {
        $client = new Client();
        $response = $client->get('https://api-metrika.yandex.net/stat/v1/data', [
            'headers' => [
                'Authorization' => "OAuth " . $accessToken,
            ],
            'query' => [
                'id' => $counterId,
                'date1' => $startDate,
                'date2' => $endDate,
                'metrics' => 'ym:s:visits',
                'dimensions' => 'ym:pv:date',
                'limit' => 100,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
