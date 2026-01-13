<?php

namespace App\MoonShine\Resources\TestRestUser\Pages;

use App\MoonShine\Resources\TestRestUser\TestRestUserResource;
use MoonShine\Crud\QueryTags\QueryTag;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<TestRestUserResource>
 */
class TestRestUserIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Preview::make('Avatar', 'avatar_url')->image(),
            Text::make('Name'),
            Text::make('Role', formatted: fn($data) => $data->role['name']),
            Text::make('Phone'),
            Text::make('Email'),
        ];
    }

    protected function filters(): iterable
    {
        return [
            Text::make('Name'),
            Text::make('Phone'),
            Text::make('Email'),
        ];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make('Admins', static fn(array $data) => [
                ...$data,
                'filter[role_id]' => 1
            ]),

            QueryTag::make('Managers', static fn(array $data) => [
                ...$data,
                'filter[role_id]' => 2
            ]),

            QueryTag::make('Users', static fn(array $data) => [
                ...$data,
                'filter[role_id]' => 3
            ])
        ];
    }
}
