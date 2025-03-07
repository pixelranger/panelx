<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use MoonShine\Resources\Resource;
use MoonShine\Fields\ID;
use MoonShine\Fields\Text;
use MoonShine\Fields\BelongsTo;
use MoonShine\Fields\Number;
use MoonShine\Fields\Date;
use App\Models\Site;

class SiteResource extends Resource
{
    public static string $model = Site::class; // Указываем модель
    public static string $title = 'Сайты';     // Название ресурса

    /**
     * Метод для определения полей в форме
     */
    public function fields(): array
    {
        return [
            ID::make()->sortable(), // Поле ID
            Text::make('Название', 'title')->required(), // Поле для названия
            Text::make('URL', 'url')->required(), // Поле для URL
            Text::make('URL админки', 'url_admin')->required(), // Поле для URL админки
            Number::make('Прогноз дохода', 'income_forecast')->default(0), // Прогноз дохода
            BelongsTo::make('Автор', 'createdBy', 'name')->default(auth()->id())->readonly(), // Автор
            Date::make('Дата создания', 'created_at')->format('d.m.Y')->sortable(), // Дата создания
            Date::make('Дата обновления', 'updated_at')->format('d.m.Y')->sortable(), // Дата обновления
        ];
    }

    /**
     * Добавление страницы ресурса
     * Необходимо для корректной работы ресурса в MoonShine
     */
    protected function pages(): array
    {
        return [
            // Переопределите или добавьте свои страницы, если нужно
            'index' => [
                'title' => 'Сайты',
                'component' => 'site-resource',
            ],
        ];
    }
}

