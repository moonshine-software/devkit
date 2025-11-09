<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PolyComment\Pages;

use App\Models\Post;
use App\Models\Project;
use App\MoonShine\Resources\PolyComment\PolyCommentResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<PolyCommentResource>
 */
class PolyCommentDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            MorphTo::make('Commentable', resource: PolyCommentResource::class)
                ->types([
                    Post::class => 'name',
                    Project::class => 'name',
                ]),
            BelongsTo::make('User'),
            Textarea::make('Text'),
        ];
    }
}
