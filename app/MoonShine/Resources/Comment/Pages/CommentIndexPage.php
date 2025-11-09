<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Comment\Pages;

use App\MoonShine\Resources\Comment\CommentResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<CommentResource>
 */
class CommentIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Post'),
            BelongsTo::make('User'),
            Text::make('Text'),
            Json::make('Data')
                ->fields([
                    Text::make('Title'),
                    Json::make('KV')->keyValue(),
                ]),
        ];
    }
}
