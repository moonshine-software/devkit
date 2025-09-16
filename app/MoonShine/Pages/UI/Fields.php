<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use App\Enums\ColorEnum;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\DTOs\Select\Option;
use MoonShine\Support\DTOs\Select\OptionProperty;
use MoonShine\Support\DTOs\Select\Options;
use MoonShine\UI\Components\FieldsGroup;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Checkbox;
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\FieldContainer;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Position;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Range;
use MoonShine\UI\Fields\RangeSlider;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Template;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Url;

class Fields extends Page
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
        return $this->title ?: 'Fields';
    }

    #[AsyncMethod]
    public function selectOptions(): JsonResponse
    {
        $options = new Options([
            new Option(label: 'Option 1', value: '1', selected: true, properties: new OptionProperty('https://cutcode.dev/images/platforms/youtube.png')),
            new Option(label: 'Option 2', value: '2', properties: new OptionProperty('https://cutcode.dev/images/platforms/youtube.png')),
        ]);

        return JsonResponse::make(data: $options->toArray());
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            FormBuilder::make()->fields([
                Box::make('Basic', [
                    ID::make(),
                    Text::make('Title'),
                    Textarea::make('Textarea'),
                    Url::make('Url'),
                    Color::make('Color'),
                    Email::make('Email'),
                    Phone::make('Phone'),
                    Password::make('Password'),
                    Number::make('Number'),
                    Date::make('Date'),
                    Checkbox::make('Checkbox'),
                    Switcher::make('Switcher'),
                    File::make('File'),
                    Image::make('Image'),
                    Preview::make('Preview', formatted: fn() => fake()->text()),
                    Position::make('Position'),
                    Fieldset::make('StackFields')->fields([
                        Text::make('Title 2'),
                        Preview::make('Preview 1', formatted: fn() => fake()->text()),
                        Preview::make('Preview 2', formatted: fn() => fake()->text()),
                    ]),

                    Template::make('Template')->fields([
                        Text::make('Title 3'),
                    ])->changeRender(fn($value, Template $ctx) => FieldContainer::make(
                        field: $ctx,
                        slot: FieldsGroup::make($ctx->getPreparedFields())->render(),
                    )),
                ]),

                Box::make('Range', [
                    Range::make('Range'),
                    DateRange::make('Date range'),
                    RangeSlider::make('Range slider'),
                ]),

                Box::make('Select', [
                    Select::make('Select default')->options([
                        1 => 'One',
                        2 => 'Two',
                    ]),

                    Select::make('Select native')->options([
                        1 => 'One',
                        2 => 'Two',
                    ])->native(),

                    Select::make('Select multiple')->options([
                        1 => 'One',
                        2 => 'Two',
                    ])->multiple(),

                    Select::make('Select group')->options([
                        'Italy' => [
                            1 => 'Rome',
                            2 => 'Milan'
                        ],
                        'France' => [
                            3 => 'Paris',
                            4 => 'Marseille'
                        ]
                    ]),

                    Select::make('Select group')->options([
                        'Italy' => [
                            1 => 'Rome',
                            2 => 'Milan'
                        ],
                        'France' => [
                            3 => 'Paris',
                            4 => 'Marseille'
                        ]
                    ]),

                    Select::make('Select async')->async($this->getRouter()->getEndpoints()->method('selectOptions')),

                    Enum::make('Enum')->attach(ColorEnum::class),
                ]),

                Box::make('Json', [
                    Json::make('Json default')->fields([
                        Text::make('Title')
                    ]),

                    Json::make('Json default')->fields([
                        Text::make('Title')
                    ])->object(),

                    Json::make('Json keyValue')->keyValue(),
                    Json::make('Json onlyValue')->onlyValue(),
                ])
            ])
        ];
	}
}
