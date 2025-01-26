<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Enums\Color as ColorEnum;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Boolean;
use MoonShine\UI\Components\Breadcrumbs;
use MoonShine\UI\Components\Card;
use MoonShine\UI\Components\Carousel;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Color;
use MoonShine\UI\Components\Dropdown;
use MoonShine\UI\Components\Files;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Icon;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Burger;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Layout\ThemeSwitcher;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Components\Loader;
use MoonShine\UI\Components\Modal;
use MoonShine\UI\Components\OffCanvas;
use MoonShine\UI\Components\Popover;
use MoonShine\UI\Components\ProgressBar;
use MoonShine\UI\Components\Rating;
use MoonShine\UI\Components\Spinner;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Thumbnails;
use MoonShine\UI\Components\Title;

class Components extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Components';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            ActionButton::make('ActionButton')->icon('users'),
            Popover::make('Popover', 'Popover'),
            Modal::make('Modal', 'Content')->open(),
            OffCanvas::make('OffCanvas', 'Content')->open(),
            Divider::make('Divider'),
            Alert::make()->content('Alert'),
            Badge::make('Badge'),
            LineBreak::make(),
            Flex::make([
                Boolean::make(true),
                Boolean::make(false),
            ]),
            LineBreak::make(),
            Box::make('Box', [

            ]),
            LineBreak::make(),
            Breadcrumbs::make(['/' => 'Home', '#' => 'Current']),
            Burger::make(),
            Collapse::make('Collapse', [
                Color::make(ColorEnum::RED)
            ]),
            LineBreak::make(),
            Dropdown::make('Dropdown', 'Dropdown'),
            LineBreak::make(),
            Files::make(['https://cutcode.dev/images/platforms/youtube.png', 'https://cutcode.dev/images/platforms/youtube.png']),
            LineBreak::make(),
            Carousel::make(['https://cutcode.dev/images/platforms/youtube.png', 'https://cutcode.dev/images/platforms/youtube.png']),

            LineBreak::make(),
            Grid::make([
                Column::make([
                    Heading::make('Heading'),
                    Icon::make('users'),
                    Rating::make(5),
                    Spinner::make(),
                ])->columnSpan(6),
                Column::make([
                    Link::make('#', 'Link'),
                    Loader::make(),
                    ProgressBar::make(5)
                ])->columnSpan(6),
            ]),
            LineBreak::make(),
            Tabs::make([
                Tabs\Tab::make('Tab 1', [
                    Thumbnails::make(['https://cutcode.dev/images/platforms/youtube.png', 'https://cutcode.dev/images/platforms/youtube.png'])
                ]),
                Tabs\Tab::make('Tab 2', [
                    Title::make('Title'),
                    ThemeSwitcher::make(),
                ])
            ]),
            LineBreak::make(),
            FlexibleRender::make('FlexibleRender'),
            LineBreak::make(),
            Box::make([
                Card::make(
                    title: fake()->sentence(3),
                    thumbnail: 'https://cutcode.dev/images/platforms/youtube.png',
                )
                    ->url(static fn() => 'https://cutcode.dev'),
            ])

        ];
	}
}
