<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\UI\Forms;
use App\MoonShine\Pages\UI\Selects;
use App\MoonShine\Resources\ShopResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Pages\UI\Fields;
use App\MoonShine\Pages\UI\Components;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\PostResource;
use App\MoonShine\Resources\CarResource;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\DeploymentResource;
use App\MoonShine\Resources\EnvironmentResource;
use App\MoonShine\Resources\ImageResource;
use App\MoonShine\Resources\MechanicResource;
use App\MoonShine\Resources\OwnerResource;
use App\MoonShine\Resources\PolyCommentResource;
use App\MoonShine\Resources\ProjectResource;
use App\MoonShine\Resources\TagResource;
use App\MoonShine\Resources\UserResource;

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

        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                CategoryResource::class,
                PostResource::class,
                CarResource::class,
                CommentResource::class,
                DeploymentResource::class,
                EnvironmentResource::class,
                ImageResource::class,
                MechanicResource::class,
                OwnerResource::class,
                PolyCommentResource::class,
                ProjectResource::class,
                TagResource::class,
                UserResource::class,
                ShopResource::class,
            ])
            ->pages([
                ...$config->getPages(),
                Fields::class,
                Components::class,
                Selects::class,
                Forms::class,
            ])
        ;
    }
}
