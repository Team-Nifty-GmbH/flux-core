<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\Support\Dashboard;

class HrDashboard extends Dashboard
{
    protected bool $hasTimeSelector = false;

    public static function getDefaultWidgets(): array
    {
        return parent::mapDefaultWidgets(
            static::$defaultWidgets ??
            [
            ]
        );
    }
}
