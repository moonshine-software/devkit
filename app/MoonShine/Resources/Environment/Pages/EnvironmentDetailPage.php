<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Environment\Pages;

use App\MoonShine\Resources\Environment\EnvironmentResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<EnvironmentResource>
 */
class EnvironmentDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name'),
            BelongsTo::make('Project'),
        ];
    }
}
