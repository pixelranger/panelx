<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\AssetManager\Css;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
// use App\MoonShine\Resources\SiteMetricResource;
use App\MoonShine\Resources\SiteResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        // $config->authEnable();
        moonshineAssets()->add([
            Css::make('/css/custom-layout.css')
        ]);


        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                SiteResource::class,
                // SiteMetricResource::class,

            ])
            ->pages([
                ...$config->getPages(),
            ])
        ;
    }
}
