<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PolyComment;

use App\Models\PolyComment;
use App\MoonShine\Resources\PolyComment\Pages\PolyCommentIndexPage;
use App\MoonShine\Resources\PolyComment\Pages\PolyCommentFormPage;
use App\MoonShine\Resources\PolyComment\Pages\PolyCommentDetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;

/**
 * @extends ModelResource<PolyComment, PolyCommentIndexPage, PolyCommentFormPage, PolyCommentDetailPage>
 */
#[Group('Comments')]
#[Order(13)]
class PolyCommentResource extends ModelResource
{
    protected string $model = PolyComment::class;

    protected string $title = 'PolyComments';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            PolyCommentIndexPage::class,
            PolyCommentFormPage::class,
            PolyCommentDetailPage::class,
        ];
    }
}
