<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Car;

use App\Models\Car;
use App\MoonShine\Resources\Car\Pages\CarDetailPage;
use App\MoonShine\Resources\Car\Pages\CarFormPage;
use App\MoonShine\Resources\Car\Pages\CarIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<Car, CarIndexPage, CarDetailPage, CarFormPage>
 */
#[Group('Cars')]
#[Order(17)]
class CarResource extends ModelResource
{
    protected string $model = Car::class;

    protected string $title = 'Cars';

    protected string $column = 'name';

    protected function pages(): array
    {
        return [
            CarIndexPage::class,
            CarDetailPage::class,
            CarFormPage::class,
        ];
    }
}
