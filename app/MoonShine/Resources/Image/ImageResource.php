<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Image;

use App\Models\Image;
use App\MoonShine\Resources\Image\Pages\ImageIndexPage;
use App\MoonShine\Resources\Image\Pages\ImageFormPage;
use App\MoonShine\Resources\Image\Pages\ImageDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Image, ImageIndexPage, ImageFormPage, ImageDetailPage>
 */
class ImageResource extends ModelResource
{
    protected string $model = Image::class;

    protected string $title = 'Images';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ImageIndexPage::class,
            ImageFormPage::class,
            ImageDetailPage::class,
        ];
    }
}
