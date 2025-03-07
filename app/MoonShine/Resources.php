<?php

declare(strict_types=1);

namespace App\MoonShine;

use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\SiteResource;

class Resources
{
    public static function resources(): array
    {
        return [
            CategoryResource::class,
            SiteResource::class,
        ];
    }
}
