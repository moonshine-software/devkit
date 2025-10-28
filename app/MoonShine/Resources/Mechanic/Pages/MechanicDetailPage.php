<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Mechanic\Pages;

use App\MoonShine\Resources\Owner\OwnerResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasOneThrough;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<ModelResource>
 */
class MechanicDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name'),
            BelongsTo::make('Shop'),
            HasOneThrough::make('CarOwner', resource: OwnerResource::class),
        ];
    }
}
