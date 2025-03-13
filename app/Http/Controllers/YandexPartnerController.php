<?php

namespace App\Http\Controllers;

use App\Services\YandexPartnerService;
use Illuminate\Http\Request;

class YandexPartnerController extends Controller
{
    protected $yandexPartnerService;

    public function __construct(YandexPartnerService $yandexPartnerService)
    {
        $this->yandexPartnerService = $yandexPartnerService;
    }

    public function showRevenue()
    {
        // Домен для тестирования
        $domain = 'oops.ru';

        // Получаем данные о доходе
        $revenueData = $this->yandexPartnerService->getRevenueData($domain);

        // Возвращаем представление с данными
        return view('yandex-partner', [
            'revenueData' => $revenueData,
            'domain' => $domain,
        ]);
    }
}
