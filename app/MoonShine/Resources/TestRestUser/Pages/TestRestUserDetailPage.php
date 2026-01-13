<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\TestRestUser\Pages;

use App\MoonShine\Resources\TestRestUser\TestRestUserResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use Throwable;

/**
 * @extends DetailPage<TestRestUserResource>
 */
class TestRestUserDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     * @throws Throwable
     */
    protected function fields(): iterable
    {
        return $this->getResource()->getIndexFields();
    }
}
