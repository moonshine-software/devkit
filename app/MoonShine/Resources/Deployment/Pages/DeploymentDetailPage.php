<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deployment\Pages;

use App\MoonShine\Resources\Deployment\DeploymentResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<DeploymentResource>
 */
class DeploymentDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name'),
            BelongsTo::make('Environment'),
        ];
    }
}
