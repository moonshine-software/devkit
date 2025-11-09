<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Comment\Pages;

use App\MoonShine\Resources\Comment\CommentResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<CommentResource>
 */
class CommentFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                BelongsTo::make('Post')
                    ->required(),
                BelongsTo::make('User')
                    ->required(),
                Textarea::make('Text')
                    ->required(),
                Json::make('Data')
                    ->fields([
                        Text::make('Title'),
                        Json::make('KV')->keyValue(),
                    ]),
            ])
        ];
    }
}
