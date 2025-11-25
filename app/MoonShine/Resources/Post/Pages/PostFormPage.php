<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Post\Pages;

use App\Enums\ColorEnum;
use App\MoonShine\Resources\Comment\CommentResource;
use App\MoonShine\Resources\Post\PostResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\RelationRepeater;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\MenuManager\MenuItem;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<PostResource>
 */
class PostFormPage extends FormPage
{
    protected function menu(): array
    {
        return [
            MenuItem::make($this->getResource()->getIndexPageUrl(), 'Вернуться назад')->icon('backward')
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name')
                ->reactive(lazy: true),
            Slug::make('Slug')
                ->from('name')
                ->live(),
            BelongsTo::make('User')
                ->nullable(),
            Textarea::make('Text'),
            Enum::make('Enums')
                ->attach(ColorEnum::class)
                ->multiple(),

            RelationRepeater::make('Comment', 'comment', resource: CommentResource::class),

            HasMany::make('Comments')
                ->creatable(),
            BelongsToMany::make('Categories')
                //->deduplication(false)
                ->fields([
                    Text::make('Pivot field'),
                ])
                ->asyncSearch()
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => ['required'],
        ];
    }
}
