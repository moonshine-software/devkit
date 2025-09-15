<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use App\MoonShine\Resources\CarResource;
use App\MoonShine\Resources\Post\PostResource;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\DTOs\Select\Option;
use MoonShine\Support\DTOs\Select\OptionGroup;
use MoonShine\Support\DTOs\Select\OptionProperty;
use MoonShine\Support\DTOs\Select\Options;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\EventParams\ToastEventParams;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Fields\Select;
use Random\RandomException;

final class Selects extends Page
{
    protected string $title = 'Selects';

    /**
     * @throws RandomException
     */
    #[AsyncMethod]
    public function select(JsonResponse $response): JsonResponse
    {
        $options = random_int(0, 1) === 1
            ? $this->getDefaultGroupedOptions()->toArray()
            : $this->getDefaultOptions()->toArray();

        return $response->setData($options);
    }

    private function getImage(): string
    {
        return 'https://devkit.moonshine.test/vendor/moonshine/avatar.jpg';
    }

    private function getDefaultOptions(): Options
    {
        return new Options([
            new Option('France', '1'),
            new Option('Russia', '2', properties: new OptionProperty($this->getImage())),
            new Option('Italy', '3'),
            new Option('USA', '4'),
        ]);
    }

    private function getDefaultGroupedOptions(): Options
    {
        return new Options([
            new OptionGroup('Russia', new Options([
                new Option('Moscow', '1'),
                new Option('Saint-Petersburg', '2', properties: new OptionProperty($this->getImage())),
            ])),

            new OptionGroup('USA', new Options([
                new Option('Los Angeles', '3'),
                new Option('New York', '4'),
            ])),
        ]);
    }

    protected function components(): iterable
    {
        $defaultOptions = $this->getDefaultOptions();
        $groupedOptions = $this->getDefaultGroupedOptions();

        return [
            Collapse::make('It is also necessary to check', [
                Link::make(app(CarResource::class)->getUrl(), 'AssociatedWith'),
                LineBreak::make(),
                Link::make(app(PostResource::class)->getUrl(), 'Reactivity'),
            ]),

            LineBreak::make(),

            Grid::make([
                Column::make([
                    Box::make('Default', [
                        Heading::make('Basic'),
                        Select::make('Select')->options($defaultOptions),

                        Heading::make('Multiple'),
                        Select::make('Select')
                            ->multiple()
                            ->options($defaultOptions),

                        Heading::make('Multiple (nullable)'),
                        Select::make('Select')
                            ->multiple()
                            ->native()
                            ->nullable()
                            ->placeholder('Select something')
                            ->options($defaultOptions),

                        Heading::make('Nullable'),
                        Select::make('Select')
                            ->placeholder('Select')
                            ->nullable()
                            ->options($defaultOptions),

                        Heading::make('Native'),
                        Select::make('Select')
                            ->native()
                            ->placeholder('Select')
                            ->nullable()
                            ->options($defaultOptions),
                    ])->icon('paper-clip'),
                ])->columnSpan(3),

                Column::make([
                    Box::make('Group', [
                        Heading::make('Basic'),
                        Select::make('Select')->options($groupedOptions),

                        Heading::make('Multiple'),
                        Select::make('Select')
                            ->multiple()
                            ->options($groupedOptions),

                        Heading::make('Nullable'),
                        Select::make('Select')
                            ->placeholder('Select')
                            ->nullable()
                            ->options($groupedOptions),

                        Heading::make('Native'),
                        Select::make('Select')
                            ->native()
                            ->placeholder('Select')
                            ->nullable()
                            ->options($groupedOptions),
                    ])->icon('academic-cap'),
                ])->columnSpan(3),

                Column::make([
                    Box::make('Advanced', [
                        Heading::make('Searchable'),
                        Select::make('Select')
                            ->options($groupedOptions)
                            ->searchable(),
                        Select::make('Select')
                            ->searchable()
                            ->multiple()
                            ->options($groupedOptions),

                        Select::make('Select')
                            ->placeholder('Select')
                            ->nullable()
                            ->searchable()
                            ->options($groupedOptions),

                        Heading::make('OnChange'),
                        Select::make('Select')
                            ->options($groupedOptions)
                            ->onChangeEvent([
                                AlpineJs::event(JsEvent::TOAST, params: ToastEventParams::make(ToastType::PRIMARY, 'Hello'))
                            ]),
                    ])->icon('home-modern'),
                ])->columnSpan(3),

                Column::make([
                    Box::make('Async', [
                        Heading::make('Default'),
                        Select::make('Select')
                            ->async(fn() => $this->getRouter()->getEndpoints()->method('select')),

                        Heading::make('On init'),
                        Select::make('Select')
                            ->asyncOnInit()
                            ->async(fn() => $this->getRouter()->getEndpoints()->method('select')),
                        Heading::make('On init (when open: false)'),
                        Select::make('Select')
                            ->asyncOnInit(false)
                            ->async(fn() => $this->getRouter()->getEndpoints()->method('select')),
                    ])->icon('tag')->dark(),
                ])->columnSpan(3),
            ]),
        ];
    }
}
