<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\TestRestUser;


use App\MoonShine\Resources\TestRestUser\Pages\TestRestUserDetailPage;
use App\MoonShine\Resources\TestRestUser\Pages\TestRestUserFormPage;
use App\MoonShine\Resources\TestRestUser\Pages\TestRestUserIndexPage;
use App\MoonShine\RestApi\AbstractRestResource;
use Closure;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use MoonShine\MenuManager\Attributes\Group;

/**
 * @extends AbstractRestResource<object, TestRestUserIndexPage, TestRestUserFormPage, TestRestUserDetailPage>
 */
#[Group('Rest users')]
final class TestRestUserResource extends AbstractRestResource
{
    protected string $title = 'Users';

    protected string $column = 'name';

    protected function pages(): array
    {
        return [
            TestRestUserIndexPage::class,
            TestRestUserFormPage::class,
            TestRestUserDetailPage::class,
        ];
    }

    public function baseUrl(): string
    {
        return 'http://127.0.0.1:8001/api';
    }

    public function listEndpoint(): Closure
    {
        return static fn(array $query): string => '/users' . ($query !== [] ? '?' .http_build_query($query) : '');
    }

    public function itemEndpoint(): Closure
    {
        return static fn(int|string|null $key, array $query): string => "/users/$key";
    }

    protected function modifyHttpClient(): Factory|PendingRequest
    {
        return parent::modifyHttpClient()->withToken('test-api-token');
    }
}
