<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category;

use App\Models\Category;
use App\MoonShine\Resources\Category\Pages\CategoryDetailPage;
use App\MoonShine\Resources\Category\Pages\CategoryFormPage;
use App\MoonShine\Resources\Category\Pages\CategoryIndexPage;
use Leeto\MoonShineTree\Resources\TreeResource;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<Category, CategoryIndexPage, CategoryFormPage, CategoryDetailPage>
 */
#[Group('Posts')]
#[Order(14)]
class CategoryResource extends TreeResource
{
    protected string $model = Category::class;

    protected string $title = 'Categories';

    protected string $column = 'name';

    protected bool $detailInModal = true;

    protected bool $createInModal = true;

    protected bool $editInModal = true;

    protected array $with = [
        'parent',
        'children',
        'image',
    ];

    protected function pages(): array
    {
        return [
            CategoryIndexPage::class,
            CategoryFormPage::class,
            CategoryDetailPage::class,
        ];
    }

    public function treeKey(): ?string
    {
        return 'parent_id';
    }

    public function sortKey(): string
    {
        return 'sorting';
    }
}
