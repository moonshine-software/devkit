<?php

declare(strict_types=1);

namespace App\MoonShine\RestApi;


use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use Leeto\FastAttributes\Attributes;
use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Crud\Attributes\DestroyHandler;
use MoonShine\Crud\Attributes\MassDestroyHandler;
use MoonShine\Crud\Attributes\SaveHandler;
use MoonShine\Crud\QueryTags\QueryTag;
use MoonShine\Crud\Resources\CrudResource;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\UI\Fields\Field;
use Throwable;

/**
 * @template TData of mixed = mixed
 * @template-covariant TIndexPage of null|CrudPageContract = null
 * @template-covariant TFormPage of null|CrudPageContract = null
 * @template-covariant TDetailPage of null|CrudPageContract = null
 * @template TException of Throwable = \Throwable
 * @template TFields of Fields = Fields
 *
 * @extends CrudResource<MoonShine, TData, TIndexPage, TFormPage, TDetailPage, ModelNotFoundException<TData>, Fields>
 */
abstract class AbstractRestResource extends CrudResource
{
    use WithRestBelongsTo;

    protected ?string $casterKeyName = 'id';

    abstract function baseUrl(): string;

    /**
     * @return Closure(array<string, mixed> $params): string
     */
    abstract function listEndpoint(): Closure;

    /**
     * @return Closure(int|string|null $key, array<string, mixed> $params): string
     */
    abstract function itemEndpoint(): Closure;

    protected function modifyHttpClient(): Factory|PendingRequest
    {
        return Http::asJson()->baseUrl($this->baseUrl());
    }

    protected function getHttpClient(): Factory|PendingRequest
    {
        return $this->modifyHttpClient();
    }

    public function getCaster(): DataCasterContract
    {
        return new JsonApiCaster(
            $this->casterKeyName,
            $this->itemsPerPage,
            $this->listEndpoint(),
        );
    }

    public function findItem(bool $orFail = false): ?DataWrapperContract
    {
        $data = $this->getHttpClient()->get(
            call_user_func($this->itemEndpoint(), $this->getItemID(), $this->getCore()->getRequest()->getAll()->toArray()),
        )->json();

        return $this->getCaster()->cast($data);
    }

    public function getItems(): iterable|Collection|LazyCollection|CursorPaginator|Paginator
    {
        $data = $this->getCore()->getRequest()->getAll()->toArray();

        if ($this->hasQueryTags()) {
            /** @var ?QueryTag $tag */
            $tag = Collection::make($this->getQueryTags())
                ->first(
                    static fn (QueryTag $tag): bool => $tag->isActive(),
                );

            if($tag) {
                $data = $tag->apply($data);
            }
        }

        return $this->getHttpClient()->get(
            call_user_func($this->listEndpoint(), $data),
        )->json();
    }

    public function massDelete(array $ids): void
    {
        if ($handler = Attributes::for($this, MassDestroyHandler::class)->first()) {
            $service = $this->getCore()->getContainer($handler->service);

            $handler->method === null
                ? $service($ids)
                : $service->{$handler->method}($ids);

            return;
        }

        foreach ($ids as $id) {
            $this->delete($this->getCaster()->cast([
                $this->casterKeyName => $id,
            ]));
        }
    }

    public function delete(DataWrapperContract $item, ?FieldsContract $fields = null): bool
    {
        $fields ??= $this->getFormFields()->onlyFields(withApplyWrappers: true);

        $fields->fill($item->toArray(), $item);

        if ($handler = Attributes::for($this, DestroyHandler::class)->first()) {
            $service = $this->getCore()->getContainer($handler->service);

            return $handler->method === null
                ? $service($item->getOriginal())
                : $service->{$handler->method}($item->getOriginal());
        }

        return $this->getHttpClient()->delete(
            call_user_func($this->itemEndpoint(), $item->getKey(), $this->getCore()->getRequest()->getAll()->toArray()),
        )->successful();
    }

    public function save(DataWrapperContract $item, ?FieldsContract $fields = null): DataWrapperContract
    {
        $fields ??= $this->getFormFields()->onlyFields(withApplyWrappers: true);

        $fields->fill($item->toArray(), $item);

        if ($handler = Attributes::for($this, SaveHandler::class)->first()) {
            $result = $this->resolveSaveHandler($handler, $item, $fields);
            $this->setItem($result);

            return $this->getCastedData();
        }

        try {
            $fields->each(static fn (FieldContract $field): mixed => $field->beforeApply($item->getOriginal()));
            $fields->each(fn (FieldContract $field): mixed => $field->apply($this->fieldApply($field), $item->getOriginal()));
            $fields->each(static fn (FieldContract $field): mixed => $field->afterApply($item->getOriginal()));
        } catch (Throwable $e) {
            throw new ResourceException($e->getMessage(), previous: $e);
        }

        if(!$item->getKey()) {
            $this->isRecentlyCreated = true;
        }

        $id = $this->getHttpClient()->{$item->getKey() ? 'put' : 'post'}(
            call_user_func($this->itemEndpoint(), $item->getKey(), $this->getCore()->getRequest()->getAll()->toArray()),
            $item->toArray(),
        )->json('id');

        $item->getOriginal()->{$this->casterKeyName} = $id;

        $casted = $this->getCaster()->cast($item->getOriginal());

        $this->setItem($casted->getOriginal());

        return $casted;
    }

    /**
     * @param DataWrapperContract<TData> $item
     * @return TData
     */
    private function resolveSaveHandler(SaveHandler $handler, DataWrapperContract $item, FieldsContract $fields): Model
    {
        $service = $this->getCore()->getContainer($handler->service);
        $resource = $this;

        $initial = clone $item;
        $data = Field::silentApply(static function () use ($item, $fields, $resource): array {
            $fields->each(static fn (FieldContract $field): mixed => $field->beforeApply($item->getOriginal()));
            $fields->each(static fn (FieldContract $field): mixed => $field->apply($resource->fieldApply($field), $item->getOriginal()));
            $fields->each(static fn (FieldContract $field): mixed => $field->afterApply($item->getOriginal()));

            return $item->toArray();
        });

        return $handler->method === null
            ? $service($initial->getOriginal(), $data)
            : $service->{$handler->method}($initial->getOriginal(), $data);
    }

    public function fieldApply(FieldContract $field): Closure
    {
        /**
         * @param TData $item
         * @return TData
         */
        return static function (mixed $item) use ($field): mixed {
            if (! $field->hasRequestValue() && ! $field->getDefaultIfExists()) {
                return $item;
            }

            $value = $field->getRequestValue() !== false ? $field->getRequestValue() : null;

            data_set($item, $field->getColumn(), $value);

            return $item;
        };
    }
}
