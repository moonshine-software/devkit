<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Post\Pages;

use App\Enums\ColorEnum;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\Post\PostResource;
use MoonShine\Contracts\UI\Collection\TableRowsContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\TableRowContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\HasOne;
use MoonShine\Laravel\Fields\Relationships\MorphToMany;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Collections\TableCells;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Table\TableRow;
use MoonShine\UI\Components\Table\TableTd;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use OpenSpout\Common\Entity\Row;
use Throwable;


/**
 * @extends IndexPage<PostResource>
 */
class PostIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Fieldset::make('Info', function (Fieldset $ctx) {
                return [
                    Text::make('Name'),
                    Slug::make('Slug'),
                    LineBreak::make(),
                    ActionButton::make('More information')
                        ->onClick(fn() => <<<'JS'
                      $event.target.closest('tr').nextElementSibling.style.display = 'table-row';
                    JS)
                ];
            }),

            BelongsTo::make('User')->nullable(),
            Enum::make('Enums')->attach(ColorEnum::class)->multiple(),
            Textarea::make('Text'),
        ];
    }

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component->rows(fn (TableRowsContract $default) => $default->flatMap(function (TableRow $row) {
            return [
                $row,
                TableRow::make(
                    new TableCells([
                        TableTd::make('Hello world')->style('width: 100%;')->customAttributes(['colspan' => $row->getCells()->count()]),
                    ])
                )->class('additionally')->style('display: none;')
            ];
        }));
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            BelongsTo::make('User')->nullable(),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}
