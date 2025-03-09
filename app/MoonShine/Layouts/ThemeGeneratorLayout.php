<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\UI\Components\{Components,
    Layout\Body,
    Layout\Burger,
    Layout\Content,
    Layout\Div,
    Layout\Html,
    Layout\Layout,
    Layout\Sidebar,
    Layout\ThemeSwitcher,
    Layout\Wrapper};
use MoonShine\Laravel\Layouts\CompactLayout;

final class ThemeGeneratorLayout extends AppLayout
{
    protected function menu(): array
    {
        return [];
    }

    protected function isProfileEnabled(): bool
    {
        return false;
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                Body::make([
                    Wrapper::make([
                        Sidebar::make([
                            Div::make([
                                Div::make([
                                    $this->getLogoComponent()->minimized(),
                                ])->class('menu-heading-logo'),

                                Div::make([
                                    ThemeSwitcher::make(),

                                    Div::make([
                                        Burger::make(),
                                    ])->class('menu-heading-burger'),
                                ])->class('menu-heading-actions'),
                            ])->class('menu-heading'),

                            Div::make([
                                //
                            ])->customAttributes([
                                'class' => 'menu',
                                ':class' => "asideMenuOpen && '_is-opened'",
                            ]),
                        ])->collapsed(),

                        Div::make([
                            Div::make([
                                Content::make([
                                    Components::make(
                                        $this->getPage()->getComponents(),
                                    ),
                                ]),
                            ])->class('layout-page'),
                        ])->class('flex grow overflow-auto'),
                    ]),
                ]),
            ])
                ->customAttributes([
                    'lang' => $this->getHeadLang(),
                ])
                ->withAlpineJs()
                ->withThemes(),
        ]);
    }
}
