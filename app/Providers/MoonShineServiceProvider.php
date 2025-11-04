<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\UI\Components;
use App\MoonShine\Pages\UI\Fields;
use App\MoonShine\Pages\UI\Forms;
use App\MoonShine\Pages\UI\JsonPage;
use App\MoonShine\Pages\UI\Selects;
use App\MoonShine\Pages\UI\ShowWhen;
use App\MoonShine\Pages\UI\ThemeGeneratorPage;
use App\MoonShine\Resources\Car\CarResource;
use App\MoonShine\Resources\Category\CategoryResource;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\DeploymentResource;
use App\MoonShine\Resources\EnvironmentResource;
use App\MoonShine\Resources\ImageResource;
use App\MoonShine\Resources\Mechanic\MechanicResource;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\Owner\OwnerResource;
use App\MoonShine\Resources\PolyCommentResource;
use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Project\ProjectResource;
use App\MoonShine\Resources\Shop\ShopResource;
use App\MoonShine\Resources\Tag\TagResource;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

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
                ThemeGeneratorPage::class,
                JsonPage::class,
                ShowWhen::class,
            ])
        ;
    }
}
