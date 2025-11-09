<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Comment;

use App\Models\Comment;
use App\MoonShine\Resources\Comment\Pages\CommentIndexPage;
use App\MoonShine\Resources\Comment\Pages\CommentFormPage;
use App\MoonShine\Resources\Comment\Pages\CommentDetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<Comment, CommentIndexPage, CommentFormPage, CommentDetailPage>
 */
#[Group('Comments')]
#[Order(12)]
class CommentResource extends ModelResource
{
    protected string $model = Comment::class;

    protected string $title = 'Comments';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CommentIndexPage::class,
            CommentFormPage::class,
            CommentDetailPage::class,
        ];
    }
}
