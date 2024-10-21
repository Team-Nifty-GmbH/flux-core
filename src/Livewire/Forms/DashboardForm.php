<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Dashboard\CreateDashboard;
use FluxErp\Actions\Dashboard\DeleteDashboard;
use FluxErp\Actions\Dashboard\UpdateDashboard;
use Livewire\Attributes\Locked;

class DashboardForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public bool $is_public = false;

    #[Locked]
    public ?int $copy_from_dashboard_id = null;

    public bool $createOwn = true;

    public bool $copyPublic = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateDashboard::class,
            'update' => UpdateDashboard::class,
            'delete' => DeleteDashboard::class,
        ];
    }
}
