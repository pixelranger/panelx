<?php

namespace App\Http\Controllers;

use App\Services\YandexMetrikaService;
use Illuminate\Http\Request;

class YandexMetricController extends Controller
{
    private $yandexMetrikaService;

    public function __construct(YandexMetrikaService $yandexMetrikaService)
    {
        $this->yandexMetrikaService = $yandexMetrikaService;
    }

    public function redirectToProvider()
    {
        $url = "https://oauth.yandex.ru/authorize?response_type=code&client_id=" . config('services.yandex_metrika.client_id') . "&redirect_uri=" . config('services.yandex_metrika.redirect_uri');
        return redirect($url);
    }

    public function handleProviderCallback(Request $request)
    {
        $code = $request->input('code');
        $accessToken = $this->yandexMetrikaService->getAccessToken($code);

        // Сохраните токен в сессии или базе данных
        session(['yandex_access_token' => $accessToken]);

        return redirect('/metrics');
    }

    public function getMetrics()
    {
        $accessToken = session('yandex_access_token');
        if (!$accessToken) {
            return redirect('/login-yandex');
        }

        $counterId = 'YOUR_COUNTER_ID';  // Замените на реальный ID счётчика
        $startDate = '2023-01-01';  // Замените на нужные даты
        $endDate = '2023-01-31';

        $metricsData = $this->yandexMetrikaService->getMetrics($accessToken, $counterId, $startDate, $endDate);

        return view('metrics', ['data' => $metricsData]);
    }
}
