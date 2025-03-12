<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use App\MoonShine\Resources\CarResource;
use App\MoonShine\Resources\PostResource;
use MoonShine\Advanced\Fields\RadioGroup;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\DTOs\Select\Option;
use MoonShine\Support\DTOs\Select\OptionGroup;
use MoonShine\Support\DTOs\Select\OptionProperty;
use MoonShine\Support\DTOs\Select\Options;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ToastEventParams;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\RangeSlider;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Random\RandomException;

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
