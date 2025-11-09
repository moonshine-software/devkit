<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Json;
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
