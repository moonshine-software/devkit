<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Environment;

use App\Models\Environment;
use App\MoonShine\Resources\Environment\Pages\EnvironmentDetailPage;
use App\MoonShine\Resources\Environment\Pages\EnvironmentFormPage;
use App\MoonShine\Resources\Environment\Pages\EnvironmentIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<Environment, EnvironmentIndexPage, EnvironmentFormPage, EnvironmentDetailPage>
 */
#[Group('Projects')]
#[Order(22)]
class EnvironmentResource extends ModelResource
{
    protected string $model = Environment::class;

    protected string $title = 'Environments';

    protected string $column = 'name';

    protected bool $detailInModal = true;

    protected function pages(): array
    {
        return [
            EnvironmentIndexPage::class,
            EnvironmentFormPage::class,
            EnvironmentDetailPage::class,
        ];
    }
}
