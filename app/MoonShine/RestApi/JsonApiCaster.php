<?php

declare(strict_types=1);

namespace App\MoonShine\RestApi;

use Closure;
use MoonShine\Contracts\Core\Paginator\PaginatorContract;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Core\Paginator\Paginator;
use MoonShine\Core\TypeCasts\MixedDataWrapper;

/**
 * @template T
 * @implements DataCasterContract<T>
 */
final readonly class JsonApiCaster implements DataCasterContract
{
    /**
     * @param  Closure(array<string, mixed> $params): string  $url
     */
    public function __construct(
        private string $keyName,
        private int $perPage,
        private Closure $url
    )
    {
    }

    /**
     * @return DataWrapperContract<T>
     */
    public function cast(mixed $data): DataWrapperContract
    {
        if(empty($data)) {
            return new MixedDataWrapper((object) []);
        }

        if(is_array($data)) {
            return new MixedDataWrapper(
                (object) [
                    ...$data,
                    $this->keyName => $data[$this->keyName],
                ],
                $data[$this->keyName]
            );
        }

        return new MixedDataWrapper($data, $data->{$this->keyName});
    }

    public function paginatorCast(mixed $data): ?PaginatorContract
    {
        if(!isset($data['links'])) {
            return null;
        }

        $page = request()->integer('page', 1);
        $queryWithPage = static function (int $page = 1) {
            $query = moonshine()->getRequest()->getExcept('page');
            $query['page'] = $page;

            return $query;
        };

        return new Paginator(
            path: $data['path'],
            links: array_map(static function($link) use($queryWithPage) {
                $link['url'] .= '&' . http_build_query($queryWithPage($link['page'] ?? 1));

                return $link;
            }, $data['links']),
            data: $data['data'],
            originalData: $data['data'],
            currentPage: $page,
            from: $data['from'],
            to: $data['to'],
            perPage: $this->perPage,
            simple: false,
            total: $data['total'],
            lastPage: $data['last_page'],
            firstPageUrl: $data['first_page_url'],
            prevPageUrl: call_user_func($this->url, $queryWithPage($page - 1)),
            lastPageUrl: $data['last_page_url'],
            nextPageUrl: call_user_func($this->url, $queryWithPage($page + 1)),
            pageName: 'page',
            translates: [
                'previous' => 'moonshine::pagination.previous',
                'next' => 'moonshine::pagination.next',
                'showing' => 'moonshine::pagination.showing',
                'to' => 'moonshine::pagination.to',
                'of' => 'moonshine::pagination.of',
                'results' => 'moonshine::pagination.results',
            ]
        );
    }
}
