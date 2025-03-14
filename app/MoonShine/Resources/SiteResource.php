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
use App\MoonShine\Fields\CustomPreview;

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
}
