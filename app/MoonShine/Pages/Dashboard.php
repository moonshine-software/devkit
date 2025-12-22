<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Project\ProjectResource;
use App\MoonShine\Resources\User\UserResource;
use MoonShine\Apexcharts\Components\DonutChartMetric;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Apexcharts\Components\RawChartMetric;
use MoonShine\Apexcharts\Support\SeriesItem;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Support\Enums\Layer;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

#[SkipMenu]
class Dashboard extends Page
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
        return $this->title ?: 'Dashboard';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            FormBuilder::make()
                ->fields([
                    Select::make('Section')->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5])->multiple(),
                    Text::make('Title'),
                    Select::make('Select')->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5])
                        ->multiple()
                        ->showWhen('section', 'in', [2])
                    ,
                ]),
            Grid::make([
                ValueMetric::make('Metric')
                    ->value(100)
                    ->columnSpan(6),

                ValueMetric::make('Metric')
                    ->value(100)
                    ->columnSpan(6),

                DonutChartMetric::make('Подписчики')
                    ->columnSpan(4)
                    ->values(['CutCode' => 10000, 'Apple' => 9999]),

                LineChartMetric::make('Заказы')
                    ->columnSpan(4)
                    ->series([
                        SeriesItem::make('Выручка 1', [
                            now()->format('Y-m-d') => 100,
                            now()->addDay()->format('Y-m-d') => 200,
                            now()->addDays(2)->format('Y-m-d') => 500,
                            now()->addDays(3)->format('Y-m-d') => 700,
                        ])->color('#EC4176'),
                        SeriesItem::make('Выручка 2', [
                            now()->format('Y-m-d') => 300,
                            now()->addDay()->format('Y-m-d') => 400,
                            now()->addDays(2)->format('Y-m-d') => 300,
                            now()->addDays(3)->format('Y-m-d') => 800,
                        ])->color('#85737E'),
                        SeriesItem::make('Выручка 3', [
                            now()->format('Y-m-d') => 400,
                            now()->addDay()->format('Y-m-d') => 500,
                            now()->addDays(2)->format('Y-m-d') => 400,
                            now()->addDays(3)->format('Y-m-d') => 600,
                        ])->color('#1e96fc'),
                    ]),

                RawChartMetric::make('Interactive Radar Chart')
                    ->columnSpan(4)
                    ->config([
                        'chart' => [
                            'type' => 'radar',
                            'height' => 350,
                        ],
                        'series' => [
                            [
                                'name' => 'Current Year',
                                'data' => [20, 90, 45, 75, 60],
                            ],
                        ],
                        'xaxis' => [
                            'categories' => ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
                        ],
                    ])
            ]),

            Tabs::make([
                Tabs\Tab::make('Users', [
                    app(UserResource::class)->getIndexPage()?->getListComponent(),
                ]),

                Tabs\Tab::make('Posts', [
                    ...app(PostResource::class)
                        ->getIndexPage()
                        ?->getLayerComponents(Layer::TOP),

                    app(PostResource::class)->getIndexPage()?->getListComponent(),
                ]),

                Tabs\Tab::make('Projects', [
                    app(ProjectResource::class)->getIndexPage()?->getListComponent(),
                ]),
            ])
        ];
	}
}
