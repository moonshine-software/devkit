<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Image\Pages;

use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Image\ImageResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Url;

/**
 * @extends IndexPage<ImageResource>
 */
class ImageIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Url::make('Url'),
            MorphTo::make('Imageable', resource: ImageResource::class)
                ->types([
                    Category::class => 'name',
                    Post::class => 'name',
                    Project::class => 'name',
                ]),
        ];
    }
}
