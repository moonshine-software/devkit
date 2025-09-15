<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Post\Pages;

use App\MoonShine\Resources\Post\PostResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Support\ListOf;
use Throwable;

/**
 * @extends DetailPage<PostResource>
 */
class PostDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     * @throws Throwable
     */
    protected function fields(): iterable
    {
        return $this->getResource()->getIndexFields();
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}
