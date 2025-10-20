<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use App\Models\PolyComment;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<PolyComment>
 */
class PolyCommentResource extends ModelResource
{
    protected string $model = PolyComment::class;

    protected string $title = 'PolyComments';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            MorphTo::make('Commentable', resource: PolyCommentResource::class)->types([
                Post::class => 'name',
                Project::class => 'name',
            ]),
            BelongsTo::make('User'),
            Textarea::make('Text'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make($this->indexFields())
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    /**
     * @param PolyComment $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
