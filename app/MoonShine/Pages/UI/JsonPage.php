<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\UI;

use Closure;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

#[Group('UI')]
#[Order(6)]
class JsonPage extends Page
{
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return 'Json';
    }

    public function fieldApply(FieldContract $field): Closure
    {
        return static function (mixed $item) use ($field): mixed {
            if (! $field->hasRequestValue() && ! $field->getDefaultIfExists()) {
                return $item;
            }

            $value = $field->getRequestValue() !== false ? $field->getRequestValue() : null;

            data_set($item, $field->getColumn(), $value);

            return $item;
        };
    }

    #[AsyncMethod]
    public function apply(CrudRequestContract $request): JsonResponse
    {
        $fields = match ($request->input('_type')) {
            'default' => $this->defaultFields(),
            'key-value' => $this->keyValueFields(),
            'only-value' => $this->onlyValueFields(),
            'object' => $this->objectFields(),
            'create-button-options' => $this->createButtonOptionsFields(),
            'display-options' => $this->displayOptionsFields(),
            'mixed-object' => $this->mixedObjectFields(),
            'mixed-default' => $this->mixedDefaultFields(),
            default => $this->defaultFields(),
        };

        $fields = $fields->onlyFields();

        $data = new class extends Model {};

        $fields->each(static fn (FieldContract $field): mixed => $field->beforeApply($data));

        $fields
            ->withoutOutside()
            ->each(fn (FieldContract $field): mixed => $field->apply($this->fieldApply($field), $data));

        $fields->each(static fn (FieldContract $field): mixed => $field->afterApply($data));

        return JsonResponse::make()
            ->html([
                '.after-apply-json' => '<code>'.json_encode($data->getAttributes(), JSON_THROW_ON_ERROR).'</code>',
            ]);
    }

    public function components(): array
    {
        return [
            Div::make([])->class('after-apply-json'),
            LineBreak::make(),

            Grid::make([
                Column::make([
                    Box::make('Default', [
                        $this->form(
                            $this->defaultFields(),
                        ),

                        Divider::make('Filled'),

                        $this->form(
                            $this->defaultFields(),
                            $this->defaultFill(),
                        ),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('KeyValue', [
                        $this->form(
                            $this->keyValueFields(),
                        ),
                        Divider::make('Filled'),
                        $this->form(
                            $this->keyValueFields(),
                            $this->keyValueFill(),
                        ),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('OnlyValue', [
                        $this->form(
                            $this->onlyValueFields(),
                        ),
                        Divider::make('Filled'),
                        $this->form(
                            $this->onlyValueFields(),
                            $this->onlyValueFill(),
                        ),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('Object', [
                        $this->form(
                            $this->objectFields(),
                        ),
                        Divider::make('Filled'),
                        $this->form(
                            $this->objectFields(),
                            $this->objectFill(),
                        ),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('Table (Preview)', [
                        $this->tableFields()
                            ->findByColumn('data')
                            ?->fillData($this->tableFill()),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('Create Button Options', [
                        $this->form(
                            $this->createButtonOptionsFields(),
                        ),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('Display Options', [
                        $this->form(
                            $this->displayOptionsFields(),
                            $this->displayOptionsFill(),
                        ),
                    ]),
                ])->columnSpan(12),

                Column::make([
                    Box::make('Mixed Object', [
                        $this->form(
                            $this->mixedObjectFields(),
                        ),
                        Divider::make('Filled'),
                        $this->form(
                            $this->mixedObjectFields(),
                            $this->mixedObjectFill(),
                        ),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('Mixed Object (Preview)', [

                        $this->mixedObjectFields()
                            ->findByColumn('data')
                            ?->previewMode()
                            ?->fillData($this->mixedObjectFill()),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make('Mixed Default', [
                        $this->form(
                            $this->mixedDefaultFields(),
                        ),
                        Divider::make('Filled'),
                        $this->form(
                            $this->mixedDefaultFields(),
                            $this->mixedDefaultFill(),
                        ),
                    ]),
                ])->columnSpan(12),

                Column::make([
                    Box::make('Nested Table (Preview)', [

                        $this->nestedTableFields()
                            ->findByColumn('data')
                            ?->fillData($this->nestedTableFill()),
                    ]),
                ])->columnSpan(12),

                Column::make([
                    Box::make('Mixed Default (Preview)', [

                        $this->mixedDefaultFields()
                            ->findByColumn('data')
                            ?->previewMode()
                            ?->fillData($this->mixedDefaultFill()),
                    ]),
                ])->columnSpan(12),
            ]),
        ];
    }

    private function displayOptionsFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('display-options'),

            Json::make('Vertical orientation', 'vertical_orientation')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ], 'vertical'),

            Json::make('Reorderable', 'reorderable')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ])
                ->reorderable(),

            Json::make('Custom buttons', 'custom_buttons')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ])
                ->buttons([
                    ActionButton::make('Remove')
                        ->icon('trash')
                        ->onClick(fn (): string => 'remove()', 'prevent')
                        ->error()
                        ->showInLine(),
                ]),

            Json::make('Custom empty message', 'custom_empty_message')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ])
                ->emptyMessage('No demo rows'),
        ]);
    }

    private function displayOptionsFill(): array
    {
        return [
            'vertical_orientation' => [
                [
                    'title' => 'Vertical row',
                    'value' => 'Fields are stacked',
                ],
            ],
            'reorderable' => [
                [
                    'title' => 'First',
                    'value' => 'Drag me',
                ],
                [
                    'title' => 'Second',
                    'value' => 'Drag me too',
                ],
            ],
            'custom_buttons' => [
                [
                    'title' => 'Custom action',
                    'value' => 'Uses custom row button',
                ],
            ],
            'custom_empty_message' => [],
        ];
    }

    private function createButtonOptionsFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('create-button-options'),

            Json::make('Icon only with Limit', 'icon_only')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ])
                ->creatable(limit: 2, showButtonText: false),

            Json::make('Text only', 'text_only')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ])
                ->creatable(showButtonIcon: false),

            Json::make('Hidden add button', 'hidden_add_button')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value'),
                ])
                ->creatable(hideButton: true),
        ]);
    }

    private function tableFields(): Fields
    {
        return Fields::make([
            Json::make('Data')
                ->fields([
                    Text::make('Title'),
                    Select::make('Status')->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
                    Number::make('Stock')->nullable(),
                    Switcher::make('Active'),
                ])
                ->table(),
        ]);
    }

    private function tableFill(): array
    {
        return [
            'data' => [
                [
                    'title' => 'First product',
                    'status' => 'draft',
                    'stock' => 10,
                    'active' => true,
                ],
                [
                    'title' => 'Second product',
                    'status' => 'published',
                    'stock' => 25,
                    'active' => false,
                ],
            ],
        ];
    }

    private function nestedTableFields(): Fields
    {
        return Fields::make([
            Json::make('Data')
                ->fields([
                    Text::make('Name'),

                    Json::make('Prices')
                        ->fields([
                            Number::make('Wholesale price'),
                            Number::make('Retail price'),
                            Switcher::make('Tax included'),
                        ])
                        ->object()
                        ->table(),

                    Json::make('Links')
                        ->fields([
                            Text::make('Label'),
                            Text::make('Url'),
                        ])
                        ->table(),
                ])
                ->table(),
        ]);
    }

    private function nestedTableFill(): array
    {
        return [
            'data' => [
                [
                    'name' => 'Product 1',
                    'prices' => [
                        'wholesale_price' => 1000,
                        'retail_price' => 1200,
                        'tax_included' => true,
                    ],
                    'links' => [
                        [
                            'label' => 'Docs',
                            'url' => 'https://moonshine-laravel.com',
                        ],
                        [
                            'label' => 'GitHub',
                            'url' => 'https://github.com/moonshine-software',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function defaultFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('default'),

            Json::make('Data')
                ->fields([
                    Text::make('Title'),
                    Text::make('Value')->showWhenRow('title', 'test'),
                ])->removable(),
        ]);
    }

    private function defaultFill(): array
    {
        return [
            'data' => [
                ['title' => 'Title 1', 'value' => 'Value 1'],
                ['title' => 'Title 2', 'value' => 'Value 2'],
            ],
        ];
    }

    private function keyValueFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('key-value'),

            Json::make('Data')->removable()->keyValue(),
        ]);
    }

    private function keyValueFill(): array
    {
        return [
            'data' => [
                'key 1' => 'value 1',
                'key 2' => 'value 2',
                'key 3' => 'value 3',
            ],
        ];
    }

    private function onlyValueFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('only-value'),

            Json::make('Data')->removable()->onlyValue(),
        ]);
    }

    private function onlyValueFill(): array
    {
        return [
            'data' => [
                'value 1',
                'value 2',
                'value 3',
            ],
        ];
    }

    private function objectFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('object'),

            Json::make('Data')->fields([
                Text::make('Title'),
                Text::make('Value'),

                Json::make('Inner')->fields([
                    Text::make('One'),
                    Text::make('Two'),
                    Number::make('Number')->step(0.1)->nullable(),
                    Switcher::make('Active'),
                ])->object(),
            ])->object(),
        ]);
    }

    private function objectFill(): array
    {
        return [
            'data' => [
                'title' => 'Title',
                'value' => 'Value',
                'inner' => [
                    'one' => 'One',
                    'two' => 'Two',
                ],
            ],
        ];
    }

    private function mixedObjectFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('mixed-object'),

            Json::make('Data')->fields([
                Text::make('Title'),
                Text::make('Value'),

                Json::make('Inner')->fields([
                    Text::make('One'),
                    Text::make('Two'),

                    Json::make('KV')->keyValue(),
                ])->object(),
            ])->object(),
        ]);
    }

    private function mixedObjectFill(): array
    {
        return [
            'data' => [
                'title' => 'Title',
                'value' => 'Value',
                'inner' => [
                    'one' => 'One',
                    'two' => 'Two',
                    'kv' => [
                        'key 1' => 'value 1',
                        'key 2' => 'value 2',
                        'key 3' => 'value 3',
                    ],
                ],
            ],
        ];
    }

    private function mixedDefaultFields(): Fields
    {
        return Fields::make([
            Hidden::make('_type')->setValue('mixed-default'),

            Json::make('Data')->fields([
                Text::make('Title'),

                Json::make('Object')->fields([
                    Text::make('Title'),
                    Text::make('Value'),

                    Json::make('Inner')->fields([
                        Fieldset::make('fieldset', [
                            Flex::make([
                                Text::make('One'),
                                Text::make('Two'),
                            ]),
                        ]),

                        Select::make('Multiple')->options([1 => 1, 2 => 2, 3 => 3])->multiple(),

                        Json::make('KV')->keyValue(),
                    ])->object(),
                ])->object(),
            ]),
        ]);
    }

    private function mixedDefaultFill(): array
    {
        return [
            'data' => [
                [
                    'title' => 'Title',
                    'object' => [
                        'title' => 'Title',
                        'value' => 'Value',
                        'inner' => [
                            'one' => 'One',
                            'two' => 'Two',
                            'kv' => [
                                'key 1' => 'value 1',
                                'key 2' => 'value 2',
                                'key 3' => 'value 3',
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Title 2',
                    'object' => [
                        'title' => 'Title 2',
                        'value' => 'Value 2',
                        'inner' => [
                            'one' => 'One 2',
                            'two' => 'Two 2',
                            'kv' => [
                                'key 1 2' => 'value 1 2',
                                'key 2 2' => 'value 2 2',
                                'key 3 2' => 'value 3 2',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function form(Fields $fields, array $fill = []): FormBuilder
    {
        return FormBuilder::make()
            ->asyncMethod('apply')
            ->fields($fields)
            ->fill($fill);
    }
}
