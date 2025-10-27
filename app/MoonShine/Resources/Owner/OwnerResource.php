<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Owner;

use App\Models\Owner;
use App\MoonShine\Resources\Owner\Pages\OwnerDetailPage;
use App\MoonShine\Resources\Owner\Pages\OwnerFormPage;
use App\MoonShine\Resources\Owner\Pages\OwnerIndexPage;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Owner, OwnerIndexPage, OwnerDetailPage, OwnerFormPage>
 */
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
