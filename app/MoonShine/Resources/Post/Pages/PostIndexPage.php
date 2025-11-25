<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Post\Pages;

use App\MoonShine\Resources\Comment\CommentResource;
use App\MoonShine\Resources\Post\PostResource;
use MoonShine\Contracts\UI\Collection\TableRowsContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\RelationRepeater;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\MenuManager\MenuItem;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\Enums\ListRowEventType;
use MoonShine\Support\EventParams\ListRowEventParams;
use MoonShine\Support\ListOf;
use MoonShine\UI\Collections\TableCells;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Table\TableRow;
use MoonShine\UI\Components\Table\TableTd;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

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
                    Number::make('Stars', formatted: fn() => 3)->stars(),
                    Text::make('Name')->updateInPopover($this->getListComponentName()),
                    Slug::make('Slug'),
                    LineBreak::make(),
                    ActionButton::make('More information')
                        ->onClick(fn() => <<<'JS'
                          $event.target.closest('tr').nextElementSibling.style.display = 'table-row';
                          JS,
                        ),
                ];
            }),

            BelongsTo::make('User'),
            Textarea::make('Text'),
            RelationRepeater::make('Comment', 'comment', resource: CommentResource::class),
            HasMany::make('Comments'),
            BelongsToMany::make('Categories')
                ->inLine()
        ];
    }

    #[AsyncMethod]
    public function update48Row(JsonResponse $response): JsonResponse
    {
        return $response->events([
            AlpineJs::event(
                JsEvent::TABLE_ROW_UPDATED, $this->getListComponentName(), ListRowEventParams::make(48)
            )
        ]);
    }

    #[AsyncMethod]
    public function deleteRow(JsonResponse $response): JsonResponse
    {
        $id = $this->getResource()->getItemID();
        $this->getResource()->getQuery()->find($id)->delete();

        return $response->events([
            AlpineJs::event(
                JsEvent::TABLE_ROW_UPDATED, $this->getListComponentName(), ListRowEventParams::make($id, ListRowEventType::REMOVE)
            )
        ]);
    }

    protected function topLeftButtons(): ListOf
    {
        return parent::topLeftButtons()->add(
            ActionButton::make('Update row ID 48')->method('update48Row')
        );
    }

    protected function buttons(): ListOf
    {
        return parent::buttons()->add(
            ActionButton::make('Delete and remove')->method('deleteRow')
        );
    }

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component->rows(fn(TableRowsContract $default) => $default->flatMap(function (TableRow $row) {
            return [
                $row,
                TableRow::make(
                    new TableCells([
                        TableTd::make('Hello world')->style('width: 100%;')->customAttributes(
                            ['colspan' => $row->getCells()->count()],
                        ),
                    ]),
                )->class('additionally')->style('display: none;'),
            ];
        }))->columnSelection()->sticky()->stickyButtons();
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
}
