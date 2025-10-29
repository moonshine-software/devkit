<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Car\Pages;

use App\MoonShine\Resources\Car\CarResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<CarResource>
 */
class CarIndexPage extends IndexPage
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
            BelongsTo::make('Mechanic'),
        ];
    }
}
