<?php

namespace App\Http\Controllers;

use App\Models\OauthTokens;
use App\Services\YandexMetrikaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YandexMetricController extends Controller
{
    private $yandexMetrikaService;

    public function __construct(YandexMetrikaService $yandexMetrikaService)
    {
        $this->yandexMetrikaService = $yandexMetrikaService;
    }

    public function redirectToProvider()
    {
        $url = "https://oauth.yandex.ru/authorize?response_type=code&force_confirm=yes&client_id=" . config('services.yandex_metrika.client_id') . "&redirect_uri=" . config('services.yandex_metrika.redirect_uri');
        return redirect($url);
    }

    public function handleProviderCallback(Request $request)
    {
        $code = $request->input('code');
        $response = $this->yandexMetrikaService->getAccessToken($code);

        OauthTokens::updateOrCreate(
            ['moonshine_user_id' => auth('moonshine')->id()],
            [
                'access_token'  => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_at'    => $response['expires_in'],
            ]
        );
        return redirect('/admin');
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
