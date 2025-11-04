<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deployment;

use App\Models\Deployment;
use App\MoonShine\Resources\Deployment\Pages\DeploymentDetailPage;
use App\MoonShine\Resources\Deployment\Pages\DeploymentFormPage;
use App\MoonShine\Resources\Deployment\Pages\DeploymentIndexPage;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Deployment, DeploymentIndexPage, DeploymentFormPage, DeploymentDetailPage>
 */
class DeploymentResource extends ModelResource
{
    protected string $model = Deployment::class;

    protected string $title = 'Deployments';

    protected string $column = 'name';

    protected function pages(): array
    {
        return [
            DeploymentIndexPage::class,
            DeploymentFormPage::class,
            DeploymentDetailPage::class,
        ];
    }
}
