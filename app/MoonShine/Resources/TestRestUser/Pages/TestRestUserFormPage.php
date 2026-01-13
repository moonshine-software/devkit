<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\TestRestUser\Pages;

use App\MoonShine\Resources\TestRestUser\TestRestUserResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Text;
use Throwable;


/**
 * @extends FormPage<TestRestUserResource>
 */
class TestRestUserFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     * @throws Throwable
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),

                $this->getResource()->relationSelect(
                    'roles',
                    'Role id',
                    'role_id',
                    'data',
                    'id',
                    'name',
                    'role.id',
                    'role.name',
                ),

                Image::make('Avatar', 'avatar')
                    ->canApply(fn() => false)
                    ->disk('api')
                    ->dir('avatars'),

                Text::make('Name'),
                Text::make('Phone'),
                Text::make('Email'),
                Collapse::make('Password', [
                    Password::make('Password'),
                ])
            ])
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => ['required'],
            'phone' => ['required'],
            'email' => ['required'],
        ];
    }
}
