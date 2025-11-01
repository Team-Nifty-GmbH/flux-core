<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\Support\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected bool $hasTimeSelector = false;

    public static function getDefaultWidgets(): array
    {
        return parent::mapDefaultWidgets(
            static::$defaultWidgets ?? []
        );
    }
}
