<?php

// declare(strict_types=1);

// namespace App\MoonShine\Resources;

// use Illuminate\Database\Eloquent\Model;
// use App\Models\SiteMetric;
// use App\Models\Site;
// use MoonShine\Laravel\Resources\ModelResource;
// use MoonShine\UI\Components\Layout\Box;
// use MoonShine\Laravel\Fields\Relationships\BelongsTo;
// use MoonShine\UI\Fields\ID;
// use MoonShine\UI\Fields\Date;
// use MoonShine\UI\Fields\Number;
// use App\MoonShine\Resources\SiteResource;
// use MoonShine\Contracts\UI\FieldContract;
// use MoonShine\Contracts\UI\ComponentContract;


// @extends ModelResource<SiteMetric>
/**
 *
 */
// class SiteMetricResource extends ModelResource
// {
//     protected string $model = SiteMetric::class;

//     protected string $title = 'Метрики сайтов';

//     /**
//      * @return list<FieldContract>
//      */
//     protected function indexFields(): iterable
//     {
//         return [
//             ID::make()->sortable(),
//             // BelongsTo::make('Сайт', 'site', 'title')->sortable(),
//             BelongsTo::make('Сайт', 'site', SiteResource::class)->sortable(),
//             Date::make('Дата', 'date')->sortable(),
//             Number::make('Уникальные посетители', 'unique_visitors')->sortable(),
//             Number::make('Просмотры страниц', 'page_views')->sortable(),
//             Number::make('Доход', 'total_revenue')->sortable(),
//         ];
//     }

//     /**
//      * @return list<ComponentContract|FieldContract>
//      */
//     protected function formFields(): iterable
//     {
//         return [
//             Box::make([
//                 ID::make(),
//                 BelongsTo::make('Сайт', 'site', 'title')->required(),
//                 Date::make('Дата', 'date')->required(),
//                 Number::make('Уникальные посетители', 'unique_visitors')->default(0),
//                 Number::make('Просмотры страниц', 'page_views')->default(0),
//                 Number::make('Доход', 'total_revenue')->default(0.00),
//             ])
//         ];
//     }

//     /**
//      * @return list<FieldContract>
//      */
//     protected function detailFields(): iterable
//     {
//         return $this->indexFields();
//     }

//     /**
//      * @param SiteMetric $item
//      *
//      * @return array<string, string[]|string>
//      */
//     protected function rules(mixed $item): array
//     {
//         return [
//             'site_id' => ['required', 'exists:sites,id'],
//             'date' => ['required', 'date'],
//             'unique_visitors' => ['integer', 'min:0'],
//             'page_views' => ['integer', 'min:0'],
//             'total_revenue' => ['numeric', 'min:0'],
//         ];
//     }
// }
