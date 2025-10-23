<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Tag;

use App\Models\Tag;
use App\MoonShine\Resources\Tag\Pages\TagIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Crud\Contracts\Page\DetailPageContract;
use MoonShine\Crud\Contracts\Page\FormPageContract;
use MoonShine\Laravel\Fields\Relationships\MorphToMany;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

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
            FormPageContract::class,
            DetailPageContract::class,
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Name'),
            MorphToMany::make('Posts'),
            MorphToMany::make('Projects'),
        ];
    }

    /**
     * @return list<FieldContract|ComponentContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make($this->indexFields()),
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
     * @param  Tag  $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make('Deleted', static fn(Builder $q) => $q->onlyTrashed()),
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
