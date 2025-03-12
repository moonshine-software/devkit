<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\UI\Components;
use App\MoonShine\Pages\UI\Fields;
use App\MoonShine\Pages\UI\Forms;
use App\MoonShine\Pages\UI\JsonPage;
use App\MoonShine\Pages\UI\Selects;
use App\MoonShine\Pages\UI\ShowWhen;
use App\MoonShine\Pages\UI\ThemeGeneratorPage;
use App\MoonShine\Resources\CarResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\DeploymentResource;
use App\MoonShine\Resources\EnvironmentResource;
use App\MoonShine\Resources\ImageResource;
use App\MoonShine\Resources\MechanicResource;
use App\MoonShine\Resources\OwnerResource;
use App\MoonShine\Resources\PolyCommentResource;
use App\MoonShine\Resources\PostResource;
use App\MoonShine\Resources\ProjectResource;
use App\MoonShine\Resources\TagResource;
use App\MoonShine\Resources\UserResource;
use MoonShine\Laravel\Components\Layout\Search;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

final class MoonShineLayout extends AppLayout
{
    protected function menu(): array
    {
        return [
            ...parent::menu(),
            MenuGroup::make('UI', [
                MenuItem::make('Fields', Fields::class),
                MenuItem::make('Components', Components::class),
                MenuItem::make('Selects', Selects::class),
                MenuItem::make('Forms', Forms::class),
                MenuItem::make('Json', JsonPage::class),
                MenuItem::make('ShowWhen', ShowWhen::class),
                MenuItem::make('Themes', ThemeGeneratorPage::class),
            ]),
            MenuItem::make('Users', UserResource::class),
            MenuItem::make('Tags', TagResource::class),
            MenuItem::make('Images', ImageResource::class),
            MenuGroup::make('Comments', [
                MenuItem::make('Comments', CommentResource::class),
                MenuItem::make('PolyComments', PolyCommentResource::class),
            ]),
            MenuGroup::make('Posts', [
                MenuItem::make('Categories', CategoryResource::class),
                MenuItem::make('Posts', PostResource::class),
            ]),

            MenuGroup::make('Cars', [
                MenuItem::make('Mechanics', MechanicResource::class),
                MenuItem::make('Cars', CarResource::class),
                MenuItem::make('Owners', OwnerResource::class),
            ]),

            MenuGroup::make('Projects', [
                MenuItem::make('Projects', ProjectResource::class),
                MenuItem::make('Deployments', DeploymentResource::class),
                MenuItem::make('Environments', EnvironmentResource::class),
            ]),
        ];
    }
}
