<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category\Pages;

use App\MoonShine\Resources\Category\CategoryResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\MorphOne;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<CategoryResource>
 */
class CategoryDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            BelongsTo::make('Category'),
            Text::make('Name'),
            MorphOne::make('Image'),
        ];
    }
}
