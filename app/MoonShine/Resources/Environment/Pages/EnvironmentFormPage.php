<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Environment\Pages;

use App\MoonShine\Resources\Environment\EnvironmentResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<EnvironmentResource>
 */
class EnvironmentFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Name')
                    ->required(),
                BelongsTo::make('Project'),
            ])
        ];
    }
}
