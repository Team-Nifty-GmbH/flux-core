<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;

class Activities extends BaseActivities
{
    public string $modelType = \FluxErp\Models\Order::class;

    #[Modelable]
    public array $order;

    public bool $initialized = false;

    public function boot(): void
    {
        if ($this->initialized) {
            $this->skipRender();
        }

        $this->modelId = $this->order['id'];
    }

    public function render(): View|Factory|Application
    {
        $this->initialized = true;

        return parent::render();
    }
}
