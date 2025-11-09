<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Owner;

use App\Models\Owner;
use App\MoonShine\Resources\Owner\Pages\OwnerDetailPage;
use App\MoonShine\Resources\Owner\Pages\OwnerFormPage;
use App\MoonShine\Resources\Owner\Pages\OwnerIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<Owner, OwnerIndexPage, OwnerDetailPage, OwnerFormPage>
 */
#[Group('Cars')]
#[Order(18)]
class OwnerResource extends ModelResource
{
    protected string $model = Owner::class;

    protected string $title = 'Owners';

    protected string $column = 'name';

    protected function pages(): array
    {
        return [
            OwnerIndexPage::class,
            OwnerDetailPage::class,
            OwnerFormPage::class,
        ];
    }
}
