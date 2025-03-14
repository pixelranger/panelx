<?php

namespace App\MoonShine\Fields;

use MoonShine\UI\Fields\Preview;

class CustomPreview extends Preview
{
    protected bool $isHtml = false;

    // Переопределяем метод value
    public function value(callable $callback): static
    {
        $this->attributes['value'] = $callback;
        return $this;
    }

    // Добавляем поддержку метода asHtml
    public function asHtml(): static
    {
        $this->isHtml = true;
        return $this;
    }

    // Метод для рендеринга значения
    public function renderValue($item)
    {
        $value = isset($this->attributes['value']) ? call_user_func($this->attributes['value'], $item) : '';

        if ($this->isHtml) {
            return $value; // Возвращаем HTML если был вызван asHtml()
        }

        return parent::renderValue($item);
    }
}
