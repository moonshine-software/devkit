<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Image\Pages;

use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use App\MoonShine\Resources\Image\ImageResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Url;

/**
 * @extends FormPage<ImageResource>
 */
class ImageFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Url::make('Url'),
                MorphTo::make('Imageable', resource: ImageResource::class)
                    ->types([
                        Category::class => 'name',
                        Post::class => 'name',
                        Project::class => 'name',
                    ]),
            ])
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'url' => 'required'
        ];
    }
}
