<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Range;
use MoonShine\UI\Fields\RangeSlider;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

#[Group('UI')]
#[Order(7)]
final class ShowWhen extends Page
{
    protected string $title = 'ShowWhen';

    protected function components(): iterable
    {
        return [
            Box::make([
                FormBuilder::make()
                    ->fields([
                        Date::make('Date'),

                        Text::make('Title', 'title')->showWhenDate('date', '>', '2025-01-01'),

                        Text::make('Sub Title', 'sub_title')
                            ->wrapName('somewrap')
                            ->showWhen('title', 'test'),


                        Text::make('Sub Title 2', 'sub_title_2')
                            ->wrapName('somewrap')
                            ->showWhen('sub_title', 'test'),

                        DateRange::make('Date Range', 'date_range')->showWhen('title', 'test'),
                        Range::make('Range', 'range')->showWhenDate('date_range', '>', '2025-01-01'),
                        RangeSlider::make('Range slider', 'range_slider')->showWhen('range', [5, 10]),

                        Select::make('Section')
                            ->showWhen('title', 'test')
                            ->withoutWrapper()
                            ->multiple()
                            ->setValue([2])
                            ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 22 => 22])
                        ,

                        Select::make('Select')
                            ->showWhen('section', 'in', [2])
                            ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 22 => 22])
                            ->multiple()
                        ,

                        Json::make('Json')->fields([
                            Text::make('Title'),
                            Select::make('Section 2')
                                ->showWhen('section', 'in', [2])
                                ->multiple()
                                ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 22 => 22])
                            ,

                            Select::make('Section 3')
                                ->showWhenRow('section2', 'in', [2])
                                ->multiple()
                                ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 22 => 22])
                            ,
                        ]),

                        Json::make('Object')->fields([
                            Text::make('Title')->showWhen('object.inner_object.inner_title', 'test'),
                            Text::make('Value'),

                            Json::make('Inner Object')->fields([
                                Text::make('Inner Title'),
                                Text::make('Inner Value')->showWhen('object.value', 'test'),
                            ])->object()
                        ])->object(),

                        Json::make('Data')
                            ->fields([
                                Text::make('Title'),
                                Text::make('Value'),

                                Json::make('Object')->fields([
                                    Text::make('Title'),
                                    Text::make('Value')->showWhen('data.1.value', 'test'),

                                    Json::make('KV')
                                        ->showWhen('data.1.title', 'test')
                                        ->keyValue()
                                ])->object(),
                            ])
                    ]),
            ])
        ];
    }
}
