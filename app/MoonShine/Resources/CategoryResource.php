<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Category;
use MoonShine\Resources\Resource;
use MoonShine\Fields\Text;
use MoonShine\Fields\BelongsTo;
use MoonShine\Fields\SoftDelete;
use MoonShine\Fields\DateTime;

class CategoryResource extends Resource
{
    // Указываем модель
    public static string $model = Category::class;

    // Метод для получения полей формы
    public function fields(): array
    {
        return [
            Text::make('Title', 'title')
                ->required(),

            BelongsTo::make('Created By', 'created_by', \App\Models\User::class)
                ->required(),

            SoftDelete::make(), // Для мягкого удаления

            DateTime::make('Created At', 'created_at')
                ->readonly()
                ->format('d.m.Y H:i'),

            DateTime::make('Updated At', 'updated_at')
                ->readonly()
                ->format('d.m.Y H:i'),
        ];
    }

    // Обязательный метод, возвращающий страницы
    protected function pages(): array
    {
        return [
            'index' => 'categories.index',  // Роутинг на страницу индекса
            'create' => 'categories.create', // Роутинг для создания
            'edit' => 'categories.edit',     // Роутинг для редактирования
        ];
    }
}
