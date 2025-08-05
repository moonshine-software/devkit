<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use App\MoonShine\Layouts\ThemeGeneratorLayout;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Core\Attributes\Layout;
use MoonShine\Crud\Components\Layout\Notifications;
use MoonShine\Laravel\Components\Layout\Profile;
use MoonShine\Crud\Components\Layout\Search;
use MoonShine\Crud\Contracts\Notifications\MoonShineNotificationContract;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use MoonShine\Laravel\MoonShineRequest;
use MoonShine\Laravel\Notifications\MoonShineNotification;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\Color as ColorEnum;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Boolean;
use MoonShine\UI\Components\Breadcrumbs;
use MoonShine\UI\Components\CardsBuilder;
use MoonShine\UI\Components\Carousel;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Color as ColorComponent;
use MoonShine\UI\Components\Dropdown;
use MoonShine\UI\Components\Files;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Icon;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Burger;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\Menu;
use MoonShine\UI\Components\Layout\ThemeSwitcher;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Components\Loader;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Popover;
use MoonShine\UI\Components\ProgressBar;
use MoonShine\UI\Components\Rating;
use MoonShine\UI\Components\Spinner;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Thumbnails;
use MoonShine\UI\Components\Title;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Color as ColorField;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Range;
use MoonShine\UI\Fields\RangeSlider;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Layout(ThemeGeneratorLayout::class)]
class ThemeGeneratorPage extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'ThemeGeneratorPage';
    }

    #[AsyncMethod]
    public function generateTheme(CrudRequestContract $request): RedirectResponse
    {
        foreach ($request->input('colors') as $name => $value) {
            session()->put("colors.$name", $value);
        }

        foreach ($request->input('radius') as $name => $value) {
            session()->put("radius.$name", $value);
        }

        return back();
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $notifications = $this->getCore()->getContainer(MoonShineNotificationContract::class);

        if ($notifications->getAll()->isEmpty()) {
            MoonShineNotification::send('Hello world');
        }

        return [
            Div::make([
                Title::make('Make it your own.'),
                Heading::make('Choose from one of our themes or build your own.'),
            ])->class('text-center'),

            Divider::make(),

            Grid::make([
                Column::make([
                    Box::make('Buttons', [
                        Flex::make([
                            ActionButton::make('Default')->badge('1'),
                            ActionButton::make('Primary')->primary()->badge('1'),
                            ActionButton::make('Secondary')->secondary()->badge('1'),
                            ActionButton::make('Info')->info()->badge('1'),
                            ActionButton::make('Success')->success()->badge('1'),
                            ActionButton::make('Warning')->warning()->badge('1'),
                            ActionButton::make('Error')->error()->badge('1'),
                            ActionButton::make('Async', '/')->primary()->async()->badge('1'),
                        ], justifyAlign: 'start')->wrap(),
                    ]),

                    Box::make('Search', [
                        Search::make()->enabled(),
                    ]),

                    Box::make('Notifications', [
                        Notifications::make(),
                    ]),

                    Box::make('Range', [
                        FormBuilder::make()
                            ->fields([
                                Range::make('Range'),
                                DateRange::make('Date range'),
                                RangeSlider::make('Range slider'),
                            ])->hideSubmit(),
                    ]),

                    Box::make('Alert', [
                        Alert::make()->content('DEFAULT'),
                        ...array_map(
                            fn(ColorEnum $color) => Alert::make(type: $color)->content($color->name),
                            ColorEnum::cases(),
                        ),
                    ]),

                    Box::make('Badge', [
                        Flex::make([
                            Badge::make('default'),
                            ...array_map(
                                fn(ColorEnum $color) => Badge::make($color->value, color: $color),
                                ColorEnum::cases(),
                            ),
                        ], justifyAlign: 'start')
                            ->class('flex-wrap'),
                    ]),

                    Box::make('Boolean', [
                        Boolean::make(true),
                        Boolean::make(false),
                    ]),

                    Box::make('Breadcrumbs', [
                        Breadcrumbs::make([
                            '/' => 'Home',
                            '/articles' => 'Articles',
                        ]),

                        Breadcrumbs::make([
                            '/articles' => 'Articles',
                        ])->prepend('/', '', 'home'),
                    ]),

                    Box::make('Profile', [
                        Profile::make(),
                        Divider::make('with menu'),
                        Profile::make()->menu([
                            ActionButton::make('Dashboard')->icon('home-modern'),
                        ]),
                    ]),

                    Box::make('Modal/OffCanvas/Popover', [
                        ActionButton::make('Modal')->inModal('Title', 'Content'),
                        ActionButton::make('OffCanvas')->inOffCanvas('Title', 'Content'),
                        Popover::make('Popover', 'Popover')->content('Hello world'),
                    ]),

                    Box::make('Tabs', [
                        Tabs::make([
                            Tabs\Tab::make('Tab 1', [
                                'Tab 1 content',
                            ])->icon('home'),

                            Tabs\Tab::make('Tab 2', [
                                'Tab 2 content',
                            ]),
                        ]),
                    ]),
                ])->columnSpan(4),

                Column::make([
                    Box::make('Form', [
                        FormBuilder::make()
                            ->fields([
                                ID::make(),
                                Text::make('Title'),
                                Textarea::make('Textarea'),
                                ColorField::make('Color'),
                                Email::make('Email'),
                                Number::make('Number')->buttons(),
                                Date::make('Date'),
                                Checkbox::make('Checkbox'),
                                Switcher::make('Switcher'),
                                File::make('File'),
                            ])
                            ->hideSubmit(),
                    ]),

                    Grid::make([
                        Column::make([
                            Box::make('Carousel', [
                                Carousel::make(
                                    items: ['https://robohash.org/1.png', 'https://robohash.org/2.png'],
                                    alt: fake()->sentence(3),
                                ),
                            ]),
                        ])->columnSpan(6),

                        Column::make([
                            Box::make('Collapse', [
                                Collapse::make('Collapse', [
                                    'Hello world',
                                ])->icon('academic-cap'),
                            ]),
                        ])->columnSpan(6),

                        Column::make([
                            Box::make('Dropdown', [
                                Dropdown::make(
                                    'Dropdown',
                                    'Toggler',
                                    items: [
                                        ActionButton::make('Item 1')->icon('paper-clip'),
                                        ActionButton::make('Item 2')->icon('envelope'),
                                    ],
                                ),
                            ]),
                        ])->columnSpan(6),

                        Column::make([
                            Box::make('Files/Thumbnails', [
                                Files::make([
                                    '/images/thumb_1.jpg',
                                    '/images/thumb_2.jpg',
                                    '/images/thumb_3.jpg',
                                ]),

                                Thumbnails::make([
                                    'https://robohash.org/1.png',
                                    'https://robohash.org/2.png',
                                    'https://robohash.org/3.png',
                                ]),
                            ]),
                        ])->columnSpan(6),

                        Column::make([
                            Box::make('Other', [
                                Title::make('Title'),
                                Heading::make('Heading'),
                                Divider::make('Divider'),
                                Burger::make(),
                                Divider::make(),
                                ThemeSwitcher::make(),
                                Divider::make(),
                                ColorComponent::make(ColorEnum::PURPLE),
                                Divider::make(),
                                Icon::make('radio', color: ColorEnum::YELLOW),
                                Link::make('/', 'Im link')->tooltip('Tooltip')->filled()->icon('home'),
                                Loader::make(),

                                ProgressBar::make(10),
                                ...array_map(
                                    fn(ColorEnum $color) => ProgressBar::make(10, color: $color),
                                    ColorEnum::cases(),
                                ),

                                Flex::make([
                                    ProgressBar::make(10)->radial(),
                                    ...array_map(
                                        fn(ColorEnum $color) => ProgressBar::make(10, color: $color)->radial(),
                                        ColorEnum::cases(),
                                    ),
                                ], justifyAlign: 'start')
                                    ->class('flex-wrap'),
                                Rating::make(3, 1, 10),

                                Flex::make([
                                    Spinner::make(),
                                    Spinner::make('md'),
                                    Spinner::make('lg'),
                                    Spinner::make('xl'),
                                ], justifyAlign: 'start')
                                    ->class('flex-wrap'),

                                Flex::make([
                                    ...array_map(
                                        fn(ColorEnum $color) => Spinner::make(color: $color),
                                        ColorEnum::cases(),
                                    ),
                                ], justifyAlign: 'start')
                                    ->class('flex-wrap'),
                            ]),
                        ])->columnSpan(6),

                        Column::make([
                            Box::make('Metrics', [
                                ValueMetric::make('Value metric')->value(20),
                                ValueMetric::make('Value metric')->value(20)->progress(100),
                            ]),
                        ])->columnSpan(6),
                    ]),
                ])->columnSpan(8),

                Column::make([
                    Box::make('Table', [
                        TableBuilder::make()
                            ->fields([
                                ID::make()->sortable(),
                                Preview::make('Avatar')->image(),
                                Text::make('Name')->sortable(),
                                Switcher::make('Banned'),
                                Preview::make('Status')->badge(),
                            ])
                            ->items($this->tableItems())
                            ->buttons([
                                ActionButton::make('')->icon('pencil-square')->info(),
                                ActionButton::make('')->icon('eye-dropper')->primary(),
                                ActionButton::make('')->icon('trash')->error(),
                            ])
                        ,
                    ]),
                ])->columnSpan(12),

                Column::make([
                    Box::make('Cards', [
                        CardsBuilder::make()
                            ->thumbnail('avatar')
                            ->title('name')
                            ->subtitle('status')
                            ->header('banned')
                            ->overlay()
                            ->fields([
                                Switcher::make('Banned'),
                                Preview::make('Status')->badge(),
                            ])
                            ->items($this->tableItems())
                            ->buttons([
                                ActionButton::make('')->icon('pencil-square')->info(),
                                ActionButton::make('')->icon('eye-dropper')->primary(),
                                ActionButton::make('')->icon('trash')->error(),
                            ])
                        ,
                    ]),
                ])->columnSpan(12),
            ]),
        ];
    }

    private function tableItems(): array
    {
        $items = [];

        for ($i = 0; $i <= 50; $i++) {
            $items[] = [
                'id' => fake()->numerify(),
                'avatar' => "https://robohash.org/" . fake()->numerify() . ".png",
                'name' => fake()->name(),
                'banned' => fake()->boolean(),
                'status' => fake()->word(),
            ];
        }

        return $items;
    }
}
