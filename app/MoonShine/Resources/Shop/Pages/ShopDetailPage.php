<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Shop\Pages;

use App\MoonShine\Resources\Shop\ShopResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<ShopResource>
 */
class ShopDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name'),
        ];
    }
}
