<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Post;

use App\Models\Post;
use App\MoonShine\Resources\Post\Pages\PostDetailPage;
use App\MoonShine\Resources\Post\Pages\PostFormPage;
use App\MoonShine\Resources\Post\Pages\PostIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Badge;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Enums\PageType;

/**
 * @extends ModelResource<Post, PostIndexPage, PostDetailPage, PostFormPage>
 */
#[Group('Posts')]
#[Order(15)]
#[Badge('new')]
class PostResource extends ModelResource
{
    protected string $model = Post::class;

    protected string $title = 'Posts';

    protected string $column = 'name';

    protected string $queryParamPrefix = 'p_';
    protected bool $saveQueryState = true;

    protected ?PageType $redirectAfterSave = PageType::INDEX;

    protected array $with = [
        'user',
        'categories',
        'comment',
        'comment.post',
        'comment.user',
        'comments',
        'comments.post',
        'comments.user',
        'image',
        'tags',
    ];

    protected function pages(): array
    {
        return [
            PostIndexPage::class,
            PostDetailPage::class,
            PostFormPage::class,
        ];
    }
}
