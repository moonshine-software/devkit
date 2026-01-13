<?php

declare(strict_types=1);

namespace App\MoonShine\RestApi;

use Illuminate\Http\Request;
use MoonShine\Crud\JsonResponse;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Support\DTOs\Select\Option;
use MoonShine\Support\DTOs\Select\Options;
use MoonShine\UI\Fields\Select;

/**
 * @mixin AbstractRestResource
 */
trait WithRestBelongsTo
{
    #[AsyncMethod]
    public function belongsTo(Request $request, JsonResponse $response): JsonResponse
    {
        $data = $this->getHttpClient()->get($request->input('_endpoint'))->json($request->input('_wrapper') ?: null);

        $values = array_map(fn(array $value) => Option::make(
            (string) $value[$request->input('_name')], (string) $value[$request->input('_key')]
        ), $data);

        return $response->setData(
            (new Options($values))->toArray()
        );
    }

    public function getBelongsToUrl(
        string $endpoint,
        ?string $wrapper = null,
        string $key = 'id',
        string $name = 'name',
    ): string
    {
        return $this->getAsyncMethodUrl('belongsTo', params: [
            '_endpoint' => $endpoint,
            '_wrapper' => $wrapper,
            '_key' => $key,
            '_name' => $name,
        ]);
    }

    public function relationSelect(
        string $endpoint,
        string $label,
        ?string $column = null,
        ?string $wrapper = null,
        string $key = 'id',
        string $name = 'name',
        string $defaultKey = 'id',
        string $defaultName = 'name',
    ): Select
    {
        return Select::make($label, $column ?? (string) str($label)->snake())
            ->async(fn() => $this->getBelongsToUrl($endpoint, $wrapper, $key, $name))
            ->options(data_has($this->getItem(), $defaultKey) ? [data_get($this->getItem(), $defaultKey) => data_get($this->getItem(), $defaultName)] : [])
            ->asyncWithFields()
            ->asyncOnInit(false);
    }
}
