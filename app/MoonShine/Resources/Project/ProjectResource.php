<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Project;

use App\Models\Project;
use App\MoonShine\Resources\Project\Pages\ProjectDetailPage;
use App\MoonShine\Resources\Project\Pages\ProjectFormPage;
use App\MoonShine\Resources\Project\Pages\ProjectIndexPage;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Project, ProjectIndexPage, ProjectDetailPage, ProjectFormPage>
 */
class ProjectResource extends ModelResource
{
    protected string $model = Project::class;

    protected string $title = 'Projects';

    protected string $column = 'name';

    protected string $queryParamPrefix = 'pr_';

    protected function pages(): array
    {
        return [
            ProjectIndexPage::class,
            ProjectDetailPage::class,
            ProjectFormPage::class,
        ];
    }
}
