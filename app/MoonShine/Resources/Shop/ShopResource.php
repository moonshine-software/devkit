<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Shop;

use App\Models\Shop;
use App\MoonShine\Resources\Shop\Pages\ShopDetailPage;
use App\MoonShine\Resources\Shop\Pages\ShopFormPage;
use App\MoonShine\Resources\Shop\Pages\ShopIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<Shop, ShopIndexPage, ShopDetailPage, ShopFormPage>
 */
#[Group('Cars')]
#[Order(19)]
class ShopResource extends ModelResource
{
    protected string $model = Shop::class;

    protected string $title = 'Shops';

    protected string $column = 'name';

    protected bool $detailInModal = true;

    protected function pages(): array
    {
        return [
            ShopIndexPage::class,
            ShopDetailPage::class,
            ShopFormPage::class,
        ];
    }
}
