<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\AssetManager\InlineCss;
use MoonShine\AssetManager\Raw;
use MoonShine\ColorManager\ColorManager;
use MoonShine\ColorManager\Palettes\NeutralPalette;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\Crud\Components\Fragment;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\UI\Components\Layout\Body;
use MoonShine\UI\Components\Layout\BottomBar;
use MoonShine\UI\Components\Layout\Burger;
use MoonShine\UI\Components\Layout\Content;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Flash;
use MoonShine\UI\Components\Layout\Html;
use MoonShine\UI\Components\Layout\Layout;
use MoonShine\UI\Components\Layout\Menu;
use MoonShine\UI\Components\Layout\MobileBar;
use MoonShine\UI\Components\Layout\ThemeSwitcher;
use MoonShine\UI\Components\Layout\Wrapper;
use MoonShine\UI\Components\When;

final class MoonShineLayout extends AppLayout
{
    /**
     * @var null|class-string<PaletteContract>
     */
    protected ?string $palette = NeutralPalette::class;

    protected bool $bottomBar = true;

    protected bool $topBar = false;

    protected bool $sidebar = false;

    protected function getBottomBarComponent(): BottomBar
    {
        return parent::getBottomBarComponent()->alwaysVisible();
    }

    protected function assets(): array
    {
        return [
            ...parent::assets(),
            // Mobile only
            /*InlineCss::make(<<<CSS
                :root {
                    --spacing: 0.25rem;
                    --text-xs: 15px;
                    --text-sm: 15px;
                    --ms-btn-icon-size:18px;
                }
            CSS),*/
        ];
    }

    /**
     * @param  ColorManager  $colorManager
     *
     * @return void
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);
    }

    protected function menu(): array
    {
        /*
        return [
            MenuGroup::make(static fn () => __('moonshine::ui.resource.system'), [
                MenuItem::make(MoonShineUserResource::class),
                MenuItem::make(MoonShineUserRoleResource::class),
            ]),

            MenuGroup::make('UI', [
                MenuItem::make(Fields::class),
                MenuItem::make(Components::class),
                MenuGroup::make('Forms', [
                    MenuItem::make(Forms::class),
                    MenuItem::make(Selects::class),
                ]),
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
                MenuItem::make(ShopResource::class),
            ]),

            MenuGroup::make('Projects', [
                MenuItem::make(ProjectResource::class),
                MenuItem::make(DeploymentResource::class),
                MenuItem::make(EnvironmentResource::class),
            ]),
        ];
        */

        return $this->autoloadMenu(onlyIcons: true);
    }
}
