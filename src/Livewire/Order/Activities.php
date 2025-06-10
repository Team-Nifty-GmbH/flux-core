<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Support\Activities as BaseActivities;
use FluxErp\Models\Order;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Activities extends BaseActivities
{
    public bool $initialized = false;

    protected string $modelType = Order::class;

    public function render(): View|Factory|Application
    {
        $this->initialized = true;

        return parent::render();
    }

    public function boot(): void
    {
        if ($this->initialized) {
            $this->skipRender();
        }
    }
}
