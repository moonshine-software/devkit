<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use MoonShine\Advanced\Fields\RadioGroup;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Laravel\MoonShineRequest;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Range;
use MoonShine\UI\Fields\RangeSlider;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

class Forms extends Page
{
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return 'Forms';
    }

    #[AsyncMethod]
    public function validate(CrudRequestContract $request): void
    {
        $validator = Validator::make($request->all(), [
            'checkbox_1' => 'accepted',
            'checkbox_2' => 'declined',
            'switcher_1' => 'accepted',
            'switcher_2' => 'declined',
            'radiogroup' => 'declined',
            'text_1' => 'required',
            'text_2' => 'required',
            'number' => 'required|int|min:1',
            'textarea' => 'required',
            'select' => 'required',
            'color' => 'required',
            'range.from' => 'required|int|min:5',
            'range.to' => 'required|int|max:50',
            'file' => 'required',
            'date' => 'required',
            'daterang.from' => 'required',
            'daterang.to' => 'required',
            'rating' => 'required',

        ]);

        $validator->validate();
    }

    public function components(): array
	{
		return [
            Grid::make([
                Column::make([
                    Box::make('Default', [
                        FormBuilder::make()
                            ->asyncMethod('validate')
                            ->name('default')
                            ->errorsAbove(false)
                            ->fields([
                                Grid::make([
                                    Column::make([
                                        Checkbox::make('Checkbox', 'checkbox_1'),
                                    ])
                                        ->columnSpan(6),
                                    Column::make([
                                        Checkbox::make('Checkbox', 'checkbox_2')
                                            ->default(1),
                                    ])
                                        ->columnSpan(6),
                                ]),
                                Grid::make([
                                    Column::make([
                                        Switcher::make('Switcher', 'switcher_1'),
                                    ])
                                        ->columnSpan(6),
                                    Column::make([
                                        Switcher::make('Switcher', 'switcher_2')
                                            ->default(true),
                                    ])
                                        ->columnSpan(6),
                                ]),
                                RadioGroup::make('RadioGroup')->options([
                                    1 => 'Option 1',
                                    2 => 'Option 2',
                                ])
                                    ->setValue(2)
                                    ->inline(),
                                Text::make('Text', 'text_1')
                                    ->placeholder('Placeholder')
                                    ->hint('hint'),
                                Text::make('Text', 'text_2')
                                    ->placeholder('Placeholder')
                                    ->copy()
                                    ->eye()
                                    ->locked()
                                    ->prefix('USD')
                                    ->suffix('$'),
                                Number::make('Number')
                                    ->default(0)
                                    ->buttons(),
                                Textarea::make('Textarea')
                                    ->placeholder('Placeholder'),
                                Select::make('Select')
                                    ->placeholder('Placeholder')
                                    ->options([
                                        'value 1' => 'Option 1',
                                        'value 2' => 'Option 2',
                                        'value 3' => 'Option 3',
                                        'value 4' => 'Option 4',
                                        'value 5' => 'Option 5',
                                    ])
                                    ->required()
                                    ->multiple()
                                    ->nullable(),
                                Color::make('Color'),
                                RangeSlider::make('RangeSlider', 'range' )
                                    ->min(0)
                                    ->max(60)
                                    ->step(1),
                                File::make('File'),
                                Date::make('Date'),
                                DateRange::make('DateRang'),
                            ])
                            ->submit(
                                label: 'Click me',
                                attributes: ['class' => 'btn-primary']
                            ),
                    ]),
                ])
                    ->columnSpan(4),

                Column::make([
                    Box::make('Disabled', [
                        FormBuilder::make($this->getRouter()->getEndpoints()->method('validate'))
                            ->name('disabled')
                            ->errorsAbove(false)
                            ->fields([
                                Grid::make([
                                    Column::make([
                                        Checkbox::make('Checkbox')
                                            ->disabled(),
                                    ])
                                        ->columnSpan(6),
                                    Column::make([
                                        Checkbox::make('Checkbox')
                                            ->default(1)
                                            ->disabled(),
                                    ])
                                        ->columnSpan(6),
                                ]),
                                Grid::make([
                                    Column::make([
                                        Switcher::make('Switcher')
                                            ->disabled(),
                                    ])
                                        ->columnSpan(6),
                                    Column::make([
                                        Switcher::make('Switcher')
                                            ->default(true)
                                            ->disabled(),
                                    ])
                                        ->columnSpan(6),
                                ]),
                                RadioGroup::make('RadioGroup')->options([
                                    1 => 'Option 1',
                                    2 => 'Option 2',
                                ])
                                    ->setValue(2)
                                    ->inline()
                                    ->disabled(),
                                Text::make('Text')
                                    ->placeholder('Placeholder')
                                    ->hint('hint')
                                    ->disabled(),
                                Text::make('Text')
                                    ->placeholder('Placeholder')
                                    ->copy()
                                    ->eye()
                                    ->locked()
                                    ->suffix('$')
                                    ->disabled(),
                                Number::make('Number')
                                    ->default(0)
                                    ->buttons()
                                    ->disabled(),
                                Textarea::make('Textarea')
                                    ->placeholder('Placeholder')
                                    ->disabled(),
                                Select::make('Select')
                                    ->placeholder('Placeholder')
                                    ->options([
                                        'value 1' => 'Option 1',
                                        'value 2' => 'Option 2',
                                    ])
                                    ->setValue('value 2')
                                    ->nullable()
                                    ->disabled(),
                                Color::make('Color')
                                    ->disabled(),
                                RangeSlider::make('RangeSlider', 'range')
                                    ->min(0)
                                    ->max(60)
                                    ->step(1)
                                    ->disabled(),
                                File::make('File')
                                    ->disabled(),
                                Date::make('Date')
                                    ->disabled(),
                                DateRange::make('DateRang')
                                    ->disabled(),
                            ])
                            ->submit(
                                label: 'Click me',
                                attributes: ['class' => 'btn-primary', 'disabled' => true],
                            ),
                    ]),
                ])
                    ->columnSpan(4),

                Column::make([
                    Box::make('Readonly', [
                        FormBuilder::make($this->getRouter()->getEndpoints()->method('validate'))
                            ->errorsAbove(false)
                            ->name('readonly')
                            ->fields([
                                Text::make('Text')
                                    ->placeholder('Placeholder')
                                    ->hint('hint')
                                    ->readonly(),
                                Text::make('Text')
                                    ->placeholder('Placeholder')
                                    ->copy()
                                    ->eye()
                                    ->locked()
                                    ->suffix('$')
                                    ->readonly(),
                                Number::make('Number')
                                    ->default(0)
                                    ->buttons()
                                    ->readonly(),
                                Textarea::make('Textarea')
                                    ->placeholder('Placeholder')
                                    ->readonly(),
                                Color::make('Color')
                                    ->readonly(),
                                RangeSlider::make('RangeSlider', 'range')
                                    ->min(0)
                                    ->max(60)
                                    ->step(1)
                                    ->readonly(),
                                Date::make('Date')
                                    ->readonly(),
                                DateRange::make('DateRang')
                                    ->readonly(),
                            ])
                            ->hideSubmit(),
                    ]),
                ])
                    ->columnSpan(4),
            ]),
        ];
	}
}
