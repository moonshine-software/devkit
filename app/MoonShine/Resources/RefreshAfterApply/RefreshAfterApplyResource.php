<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\RefreshAfterApply;

use App\Models\Tag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Tag>
 */
class RefreshAfterApplyResource extends ModelResource
{
    protected string $model = Tag::class;

    protected string $title = 'Refresh after apply';

    protected string $column = 'name';

    protected function indexFields(): iterable
    {
        return [Text::make('Name', 'name')];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function formFields(): iterable
    {
        return [
            Text::make('Name', 'name')
                ->onApply(static function (Tag $tag, mixed $value): Tag {
                    $tag->name = strtoupper(trim($value));

                    return $tag;
                })
                ->refreshAfterApply(),
            Hidden::make('_without-redirect')->setValue('1')->canApply(static fn (): bool => false),
        ];
    }
}
