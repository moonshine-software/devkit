<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Mechanic\Pages;

use App\MoonShine\Resources\Owner\OwnerResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasOneThrough;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<ModelResource>
 */
class MechanicIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            BelongsTo::make('Shop'),
            HasOneThrough::make('CarOwner', resource: OwnerResource::class),
        ];
    }
}
