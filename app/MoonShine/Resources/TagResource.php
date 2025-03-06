<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Tag;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\MorphToMany;
use MoonShine\Laravel\Http\Responses\MoonShineJsonResponse;
use MoonShine\Laravel\MoonShineRequest;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Tag>
 */
class TagResource extends ModelResource
{
    protected string $model = Tag::class;

    protected string $title = 'Tags';

    protected string $column = 'name';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            MorphToMany::make('Posts'),
            MorphToMany::make('Projects'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make($this->indexFields()),
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
     * @param  Tag  $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make('Deleted', static fn(Builder $q) => $q->onlyTrashed()),
        ];
    }

    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->withTrashed();
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->withTrashed();
    }

    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()->prepend(
            ActionButton::make('Restore')
                ->method('restore', events: [$this->getListEventName()])
                ->withConfirm()
                ->canSee(fn(Model $model) => $model->trashed()),
            ActionButton::make('Force delete')
                ->method('forceDelete', events: [$this->getListEventName()])
                ->canSee(fn(Model $model) => $model->trashed()),


            ActionButton::make('Soft delete')
                ->method('softDelete', events: [
                    AlpineJs::event(JsEvent::TABLE_ROW_UPDATED, $this->getListComponentNameWithRow()),
                ])
                ->withConfirm()
                ->canSee(fn(Model $model) => ! $model->trashed()),
        );
    }

    public function softDelete(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->delete();

        return MoonShineJsonResponse::make()->toast('Success');
    }

    public function restore(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->restore();

        return MoonShineJsonResponse::make()->toast('Success');
    }

    public function forceDelete(MoonShineRequest $request): MoonShineJsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->forceDelete();

        return MoonShineJsonResponse::make()->toast('Success');
    }

    protected function modifyDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn(Model $model) => ! $model->trashed());
    }

    protected function modifyMassDeleteButton(ActionButtonContract $button): ActionButtonContract
    {
        return $button->canSee(fn() => request()->input('query-tag') !== 'deleted');
    }
}
