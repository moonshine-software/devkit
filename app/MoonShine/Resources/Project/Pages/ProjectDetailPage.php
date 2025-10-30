<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Project\Pages;

use App\MoonShine\Resources\Project\ProjectResource;
use MoonShine\Laravel\Fields\Relationships\HasManyThrough;
use MoonShine\Laravel\Fields\Relationships\MorphMany;
use MoonShine\Laravel\Fields\Relationships\MorphOne;
use MoonShine\Laravel\Fields\Relationships\MorphToMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<ProjectResource>
 */
class ProjectDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name'),
            MorphToMany::make('Tags')->inLine(badge: true),
            MorphOne::make('Image'),
            HasManyThrough::make('Deployments'),
            MorphMany::make('PolyComments'),
            MorphOne::make('PolyComment'),
        ];
    }
}
