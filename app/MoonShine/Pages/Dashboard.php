<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\Resources\Post\PostResource;
use App\MoonShine\Resources\Project\ProjectResource;
use App\MoonShine\Resources\UserResource;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Enums\Layer;
use MoonShine\UI\Components\Tabs;

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Dashboard';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            Tabs::make([
                Tabs\Tab::make('Users', [
                    app(UserResource::class)->getIndexPage()?->getListComponent(),
                ]),

                Tabs\Tab::make('Posts', [
                    ...app(PostResource::class)
                        ->getIndexPage()
                        ?->getLayerComponents(Layer::TOP),

                    app(PostResource::class)->getIndexPage()?->getListComponent(),
                ]),

                Tabs\Tab::make('Projects', [
                    app(ProjectResource::class)->getIndexPage()?->getListComponent(),
                ]),
            ])
        ];
	}
}
