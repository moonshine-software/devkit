<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Mechanic;

use App\Models\Mechanic;
use App\MoonShine\Resources\Mechanic\Pages\MechanicDetailPage;
use App\MoonShine\Resources\Mechanic\Pages\MechanicFormPage;
use App\MoonShine\Resources\Mechanic\Pages\MechanicIndexPage;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Mechanic, MechanicIndexPage, MechanicDetailPage, MechanicFormPage>
 */
class MechanicResource extends ModelResource
{
    protected string $model = Mechanic::class;

    protected string $title = 'Mechanics';

    protected string $column = 'name';

    protected function pages(): array
    {
        return [
            MechanicIndexPage::class,
            MechanicDetailPage::class,
            MechanicFormPage::class,
        ];
    }
}
