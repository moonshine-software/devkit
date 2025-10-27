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
use App\MoonShine\Resources\Car\CarResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\DeploymentResource;
use App\MoonShine\Resources\EnvironmentResource;
use App\MoonShine\Resources\ImageResource;
use App\MoonShine\Resources\MechanicResource;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\OwnerResource;
use App\MoonShine\Resources\PolyCommentResource;
use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\ProjectResource;
use App\MoonShine\Resources\Tag\TagResource;
use App\MoonShine\Resources\UserResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

final class MoonShineLayout extends AppLayout
{
    protected function menu(): array
    {
        return [
            MenuGroup::make(static fn () => __('moonshine::ui.resource.system'), [
                MenuItem::make(MoonShineUserResource::class),
                MenuItem::make(MoonShineUserRoleResource::class),
            ]),

            MenuGroup::make('UI', [
                MenuItem::make(Fields::class),
                MenuItem::make(Components::class),
                MenuItem::make(Selects::class),
                MenuItem::make(Forms::class),
                MenuItem::make(JsonPage::class),
                MenuItem::make(ShowWhen::class),
                MenuItem::make(ThemeGeneratorPage::class),
            ]),
            MenuItem::make(UserResource::class),
            MenuItem::make(TagResource::class),
            MenuItem::make(ImageResource::class),
            MenuGroup::make('Comments', [
                MenuItem::make(CommentResource::class),
                MenuItem::make(PolyCommentResource::class),
            ]),
            MenuGroup::make('Posts', [
                MenuItem::make(CategoryResource::class),
                MenuItem::make(PostResource::class),
            ]),

            MenuGroup::make('Cars', [
                MenuItem::make(MechanicResource::class),
                MenuItem::make(CarResource::class),
                MenuItem::make(OwnerResource::class),
            ]),

            MenuGroup::make('Projects', [
                MenuItem::make(ProjectResource::class),
                MenuItem::make(DeploymentResource::class),
                MenuItem::make(EnvironmentResource::class),
            ]),
        ];
    }
}
