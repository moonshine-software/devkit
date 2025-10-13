<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\AssetManager\InlineCss;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\AssetManager\AssetElementContract;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Crud\Components\Layout\Notifications;
use MoonShine\Crud\Components\Layout\Search;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\UI\Components\{Collapse,
    Components,
    FormBuilder,
    Layout\Body,
    Layout\Content,
    Layout\Div,
    Layout\Divider,
    Layout\Html,
    Layout\Layout,
    Layout\Wrapper,
    Link};
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\Number;

final class ThemeGeneratorLayout extends AppLayout
{
    /**
     * @return list<AssetElementContract>
     */
    protected function assets(): array
    {
        $assets = parent::assets();

        $assets[] = InlineCss::make(
            "
            :root {
              --radius: " . $this->getRadius('default') . "rem;
              --radius-sm: " . $this->getRadius('sm') . "rem;
              --radius-md: " . $this->getRadius('md') . "rem;
              --radius-lg: " . $this->getRadius('lg') . "rem;
              --radius-xl: " . $this->getRadius('xl') . "rem;
              --radius-2xl: " . $this->getRadius('2xl') . "rem;
              --radius-3xl: " . $this->getRadius('3xl') . "rem;
              --radius-full: " . $this->getRadius('full') . "px;
            }",
        );

        return $assets;
    }

    private function getRadius(?string $name = null): string|array
    {
        $r = [
            'default' => session()?->get('radius.default', '0.15'),
            'sm' => session()?->get('radius.sm', '0.075'),
            'md' => session()?->get('radius.md', '0.275'),
            'lg' => session()?->get('radius.lg', '0.3'),
            'xl' => session()?->get('radius.xl', '0.4'),
            '2xl' => session()?->get('radius.2xl', '0.5'),
            '3xl' => session()?->get('radius.3xl', '1'),
            'full' => session()?->get('radius.full', '9999'),
        ];

        if($name === null) {
            return $r;
        }

        return $r[$name];
    }

    private function getColors(?string $name = null): string|array
    {
        $colors = [
            'primary' => session()?->get('colors.primary', '#1E96FC'),
            'secondary' => session()?->get('colors.secondary', '#1D8A99'),
            'sidebar' => session()?->get('colors.sidebar', '#1b253b'),
            'content' => session()?->get('colors.content', '#272d45'),
            'dark' => session()?->get('colors.dark', '#0f172a'),
        ];

        if($name === null) {
            return $colors;
        }

        return $colors[$name];
    }

    /**
     * @param  ColorManager  $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        $colorManager
            ->primary($this->getColors('primary'))
            ->secondary($this->getColors('secondary'))
            ->background($this->getColors('sidebar'))
            ->content($this->getColors('content'))
        ;
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
            FormBuilder::make()
                ->xShow(fn() => '!minimizedMenu')
                ->asyncMethod('generateTheme')
                ->fields([
                    Collapse::make('Colors', [
                        Color::make('Primary', 'colors.primary')->setValue($this->getColors('primary')),
                        Color::make('Secondary', 'colors.secondary')->setValue($this->getColors('secondary')),
                        Color::make('Sidebar', 'colors.sidebar')->setValue($this->getColors('sidebar')),
                        Color::make('Content', 'colors.content')->setValue($this->getColors('content')),
                    ]),

                    Collapse::make('Radius', array_map(
                        fn(string $name) => Number::make("Radius ($name)", "radius.$name")
                            ->step((float) $this->getRadius($name))
                            ->setValue((float) $this->getRadius($name))
                            ->buttons(),
                        array_keys($this->getRadius()),
                    ))
                ])
                ->submit('Apply'),
        ];
    }
}
