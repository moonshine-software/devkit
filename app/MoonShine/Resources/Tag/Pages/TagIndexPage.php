<?php

namespace App\MoonShine\Resources\Tag\Pages;

use App\MoonShine\Resources\Tag\TagResource;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Crud\JsonResponse;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;

/**
 * @extends IndexPage<TagResource>
 */
class TagIndexPage extends IndexPage
{
    protected function buttons(): ListOf
    {
        return parent::buttons()->prepend(
            ActionButton::make('Restore')
                ->method('restore', events: [$this->getListEventName()], page: $this)
                ->withConfirm()
                ->canSee(fn(Model $model) => $model->trashed()),
            ActionButton::make('Force delete')
                ->method('forceDelete', events: [$this->getListEventName()], page: $this)
                ->canSee(fn(Model $model) => $model->trashed()),


            ActionButton::make('Soft delete')
                ->method('softDelete', events: [$this->getListEventName()], page: $this)
                ->withConfirm()
                ->canSee(fn(Model $model) => ! $model->trashed())
        );
    }

    #[AsyncMethod]
    public function softDelete(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->delete();

        return JsonResponse::make()->toast('Success');
    }

    #[AsyncMethod]
    public function restore(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->restore();

        return JsonResponse::make()->toast('Success');
    }

    #[AsyncMethod]
    public function forceDelete(CrudRequestContract $request): JsonResponse
    {
        $item = $request->getResource()->getItem();
        $item->forceDelete();

        return JsonResponse::make()->toast('Success');
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
