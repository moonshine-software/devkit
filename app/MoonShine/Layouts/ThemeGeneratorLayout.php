<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\AssetManager\AssetElementContract;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Components\Layout\Notifications;
use MoonShine\Laravel\Components\Layout\Search;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\UI\Components\{Components,
    Heading,
    Layout\Body,
    Layout\Burger,
    Layout\Content,
    Layout\Div,
    Layout\Html,
    Layout\Layout,
    Layout\Sidebar,
    Layout\ThemeSwitcher,
    Layout\Wrapper,
    Link};
use MoonShine\Laravel\Layouts\CompactLayout;

final class ThemeGeneratorLayout extends AppLayout
{
    private function getCompactModeKey(): string
    {
        return 'compact-theme';
    }

    private function isCompact(): bool
    {
        if(request()->has($this->getCompactModeKey()) && request()->boolean($this->getCompactModeKey())) {
            session()->put($this->getCompactModeKey(), true);
        }

        if(request()->has($this->getCompactModeKey()) && !request()->boolean($this->getCompactModeKey())) {
            session()->put($this->getCompactModeKey(), false);
        }

        return request()->boolean($this->getCompactModeKey())
               || session($this->getCompactModeKey());
    }

    /**
     * @return list<AssetElementContract>
     */
    protected function assets(): array
    {
        $assets = parent::assets();

        if($this->isCompact()) {
            $assets[] = $this->getCompactThemeCss();
        }

        return $assets;
    }

    /**
     * @param  ColorManager  $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        if($this->isCompact()) {
            $colorManager
                ->primary('#1E96FC')
                ->secondary('#1D8A99')
                ->body('249, 250, 251')
                ->dark('30, 31, 67', 'DEFAULT')
                ->dark('249, 250, 251', 50)
                ->dark('243, 244, 246', 100)
                ->dark('229, 231, 235', 200)
                ->dark('209, 213, 219', 300)
                ->dark('156, 163, 175', 400)
                ->dark('107, 114, 128', 500)
                ->dark('75, 85, 99', 600)
                ->dark('55, 65, 81', 700)
                ->dark('31, 41, 55', 800)
                ->dark('17, 24, 39', 900)
                ->successBg('209, 255, 209')
                ->successText('15, 99, 15')
                ->warningBg('255, 246, 207')
                ->warningText('92, 77, 6')
                ->errorBg('255, 224, 224')
                ->errorText('81, 20, 20')
                ->infoBg('196, 224, 255')
                ->infoText('34, 65, 124');

            $colorManager
                ->body('27, 37, 59', dark: true)
                ->dark('83, 103, 132', 50, dark: true)
                ->dark('74, 90, 121', 100, dark: true)
                ->dark('65, 81, 114', 200, dark: true)
                ->dark('53, 69, 103', 300, dark: true)
                ->dark('48, 61, 93', 400, dark: true)
                ->dark('41, 53, 82', 500, dark: true)
                ->dark('40, 51, 78', 600, dark: true)
                ->dark('39, 45, 69', 700, dark: true)
                ->dark('27, 37, 59', 800, dark: true)
                ->dark('15, 23, 42', 900, dark: true)
                ->successBg('17, 157, 17', dark: true)
                ->successText('178, 255, 178', dark: true)
                ->warningBg('225, 169, 0', dark: true)
                ->warningText('255, 255, 199', dark: true)
                ->errorBg('190, 10, 10', dark: true)
                ->errorText('255, 197, 197', dark: true)
                ->infoBg('38, 93, 205', dark: true)
                ->infoText('179, 220, 255', dark: true);
        }
    }

    protected function menu(): array
    {
        return [];
    }

    protected function isProfileEnabled(): bool
    {
        return false;
    }

    protected function sidebarSlot(): array
    {
        return [
            Search::make()->enabled(),
        ];
    }

    protected function sidebarTopSlot(): array
    {
        return [
            Notifications::make(),
            Link::make(
                $this->getPage()->getRoute([$this->getCompactModeKey() => !$this->isCompact()]),
                $this->isCompact() ? 'F' : 'C'
            ),
        ];
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                Body::make([
                    Wrapper::make([
                        $this->getSidebarComponent(),

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
                ])->class($this->isCompact() ? 'theme-minimalistic' : ''),
            ])
                ->customAttributes([
                    'lang' => $this->getHeadLang(),
                ])
                ->withAlpineJs()
                ->withThemes(),
        ]);
    }
}
