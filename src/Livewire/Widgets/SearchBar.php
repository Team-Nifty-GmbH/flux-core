<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SearchBar extends Component
{
    public bool $show = false;

    public ?string $widgetComponent = null;

    public ?int $widgetId = null;

    public string $widgetModel = '';

    protected $listeners = ['renderSearchBarWidget'];

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.widgets.search-bar');
    }

    public function renderSearchBarWidget(?string $model = null, ?int $modelId = null): void
    {
        if (! is_string($model) || ! is_int($modelId)) {
            $this->skipRender();
            $this->show = false;

            return;
        }

        $component = method_exists($model, 'getLivewireComponentWidget')
            ? livewire_component_exists(resolve_static($model, 'getLivewireComponentWidget'))
                ? resolve_static($model, 'getLivewireComponentWidget')
                : null
            : null;

        if ($component === 'widgets.generic' || ! $component) {
            $this->skipRender();
            $this->show = false;

            return;
        }

        $this->widgetComponent = $component;
        $this->widgetId = $modelId;
        $this->show = true;
    }
}
