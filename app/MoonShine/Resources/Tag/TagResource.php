<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Tag;

use App\Models\Tag;
use App\MoonShine\Resources\Tag\Pages\TagDetailPage;
use App\MoonShine\Resources\Tag\Pages\TagFormPage;
use App\MoonShine\Resources\Tag\Pages\TagIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Tag>
 */
class TagResource extends ModelResource
{
    protected string $model = Tag::class;

    protected string $title = 'Tags';

    protected string $column = 'name';

    protected function pages(): array
    {
        return [
            TagIndexPage::class,
            TagFormPage::class,
            TagDetailPage::class,
        ];
    }


    protected function modifyItemQueryBuilder(Builder $builder): Builder
    {
        return $builder->withTrashed();
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->withTrashed();
    }
}
