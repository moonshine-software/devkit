<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category\Pages;

use App\MoonShine\Resources\Category\CategoryResource;
use Leeto\MoonShineTree\View\Components\TreeComponent;
use MoonShine\Crud\Components\Fragment;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\MorphOne;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<CategoryResource>
 */
class CategoryIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Category', 'parent', resource: CategoryResource::class),
            Text::make('Name'),
            MorphOne::make('Image'),
        ];
    }

    public function getListComponentName(): string
    {
        return 'tree-component';
    }

    public function getListEventType(): JsEvent
    {
        return JsEvent::FRAGMENT_UPDATED;
    }

    protected function mainLayer(): array
    {
        return [
            ...$this->getButtons(),
            Fragment::make([
                TreeComponent::make($this->getResource()),
            ])->name('tree-component'),
        ];
    }
}
