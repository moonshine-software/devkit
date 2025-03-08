<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Post;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\MorphToMany;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Post>
 */
class PostResource extends ModelResource
{
    protected string $model = Post::class;

    protected string $title = 'Posts';

    protected string $column = 'name';

    protected array $with = [
        'user',
        'categories',
        'image',
        'tags',
    ];

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->reactive(),
            Slug::make('Slug')->from('name')->live(),
            BelongsTo::make('User'),
            Textarea::make('Text'),
            BelongsToMany::make('Categories'),
            MorphToMany::make('Tags'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make($this->indexFields()),
            ...$this->reactiveSelects(),
        ];
    }

    private function reactiveSelects(): array
    {
        $options = [1 => 1, 2 => 2];

        return [
            Select::make('Default')->options($options)->reactive(function($fields, $value, $ctx, $values) {
                return $fields;
            }),
            Select::make('Default Multiple')->multiple()->options($options)->reactive(function($fields, $value, $ctx, $values) {
                return $fields;
            }),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    /**
     * @param Post $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function filters(): iterable
    {
        return [
            BelongsTo::make('User')
                ->nullable(),
        ];
    }
}
