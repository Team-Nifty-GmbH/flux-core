<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\RendersWidgets;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

abstract class Dashboard extends Component
{
    use Actions, RendersWidgets;

    protected bool $canEdit = true;

    protected bool $hasTimeSelector = true;

    public static function getDefaultWidgets(): ?array
    {
        return static::$defaultWidgets;
    }

    public static function setDefaultWidgets(?array $defaultWidgets): void
    {
        static::$defaultWidgets = $defaultWidgets;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.support.dashboard');
    }

    public function getWidgetAttributes(): array
    {
        return [];
    }
}
