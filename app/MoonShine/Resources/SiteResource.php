<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Site;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Preview;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Layout\Box;
use Illuminate\Support\Facades\Http;
use Iodev\Whois\Factory;

/**
 * @extends ModelResource<Site>
 */
class SiteResource extends ModelResource
{
    protected string $model = Site::class;
    protected string $title = 'Сайты';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Preview::make('Фавиконка', 'favicon'),
            Text::make('Название', 'title')->sortable()->required(),
            Text::make('URL', 'url')->sortable()->required(),
            Preview::make('Уникальные посетители', 'unique_visitors'),
            Preview::make('Просмотры страниц', 'page_views'),
            Preview::make('Доход', 'total_revenue'),
            Text::make('URL админки', 'url_admin')->nullable(),
            Date::make('Дата окончания регистрации', 'domain_expiration_date')->format('d.m.Y')->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Название', 'title')->required(),
                Text::make('URL', 'url')->required(),
                Text::make('URL админки', 'url_admin')->nullable(),
                Number::make('Прогноз дохода', 'income_forecast')->default(0),
                Date::make('Дата окончания регистрации', 'domain_expiration_date')->nullable(),
            ]),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    /**
     * @param Site $item
     *
     * @return array<string, string[]|string>
     */
    protected function rules(mixed $item): array
    {
        return [
            'title' => ['required', 'string'],
            'url' => ['required', 'url'],
            'url_admin' => ['nullable', 'url'],
            'income_forecast' => ['numeric', 'min:0'],
            'domain_expiration_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Получает URL админки из API Яндекс.Метрики.
     */
    protected function fetchAdminUrlFromMetrika(Site $site): ?string
    {
        $token = config('services.yandex.token');
        $counterId = $site->counter_id;

        $response = Http::withHeaders([
            'Authorization' => "OAuth $token",
        ])->get("https://api-metrika.yandex.net/stat/v1/data", [
            'ids' => $counterId,
            'metrics' => 'ym:pv:pageviews',
            'dimensions' => 'ym:pv:URLPath',
            'filters' => "ym:pv:URLPath=~'admin|dashboard|wp-admin|panel'",
            'sort' => '-ym:pv:pageviews',
            'limit' => 1,
        ]);

        if ($response->successful() && !empty($response['data'])) {
            return $site->url . $response['data'][0]['dimensions'][0]['name'];
        }

        return null;
    }

    /**
     * Получает дату окончания регистрации домена через WHOIS.
     */
    protected function fetchDomainExpirationDate(Site $site): ?string
    {
        $whois = Factory::get()->createWhois();
        $info = $whois->loadDomainInfo(parse_url($site->url, PHP_URL_HOST));

        return $info ? $info->expirationDate->format('Y-m-d') : null;
    }

    /**
     * Перед сохранением получает URL админки и дату окончания регистрации домена.
     */
    protected function beforeSave(Site $site): void
    {
        if (!$site->url_admin) {
            $site->url_admin = $this->fetchAdminUrlFromMetrika($site);
        }

        if (!$site->domain_expiration_date) {
            $site->domain_expiration_date = $this->fetchDomainExpirationDate($site);
        }
    }
}
