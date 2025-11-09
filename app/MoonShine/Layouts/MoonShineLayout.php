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
use App\MoonShine\Resources\Category\CategoryResource;
use App\MoonShine\Resources\Comment\CommentResource;
use App\MoonShine\Resources\Deployment\DeploymentResource;
use App\MoonShine\Resources\Environment\EnvironmentResource;
use App\MoonShine\Resources\Image\ImageResource;
use App\MoonShine\Resources\Mechanic\MechanicResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use App\MoonShine\Resources\Owner\OwnerResource;
use App\MoonShine\Resources\PolyComment\PolyCommentResource;
use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Project\ProjectResource;
use App\MoonShine\Resources\Shop\ShopResource;
use App\MoonShine\Resources\Tag\TagResource;
use App\MoonShine\Resources\User\UserResource;
use MoonShine\ColorManager\ColorManager;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\Crud\Components\Fragment;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\UI\Components\Layout\Body;
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
    protected ?string $palette = PurplePalette::class;

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
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                Body::make([
                    Wrapper::make([
                        MobileBar::make([
                            Fragment::make([
                                $this->getLogoComponent()->minimized(),
                            ])->class('menu-logo'),

                            Fragment::make([
                                Divider::make('Mobile bar'),
                                Menu::make()->top(),
                            ])->class('menu menu--horizontal'),

                            Fragment::make([
                                When::make(
                                    fn (): bool => $this->isProfileEnabled(),
                                    fn (): array
                                        => [
                                        $this->getProfileComponent(),
                                    ],
                                ),
                                Div::make()->class('menu-divider menu-divider--vertical'),
                                When::make(
                                    fn (): bool => $this->hasThemes() && ! $this->isAlwaysDark(),
                                    static fn (): array => [ThemeSwitcher::make()]
                                ),
                                Div::make([
                                    Burger::make()->mobileBar(),
                                ])->class('menu-burger'),
                            ])->class('menu-actions'),
                        ]),

                        $this->getTopBarComponent(),
                        $this->getSidebarComponent(),

                        Div::make([
                            Fragment::make([
                                Flash::make(),

                                $this->getHeaderComponent(),

                                Content::make($this->getContentComponents()),

                                $this->getFooterComponent(),
                            ])->class(['layout-page', 'layout-page-simple' => $this->contentSimpled])->name(self::CONTENT_FRAGMENT_NAME),
                        ])->class(['layout-main', 'layout-main-centered' => $this->contentCentered])->customAttributes(['id' => self::CONTENT_ID]),
                    ]),
                ]),
            ])
                ->customAttributes([
                    'lang' => $this->getHeadLang(),
                ])
                ->withAlpineJs()
                ->when(
                    $this->hasThemes() || $this->isAlwaysDark(),
                    fn (Html $html): Html => $html->withThemes($this->isAlwaysDark())
                ),
        ]);
    }
}
