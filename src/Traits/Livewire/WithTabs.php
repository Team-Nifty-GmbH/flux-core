<?php

namespace FluxErp\Traits\Livewire;

use Illuminate\View\View;

trait WithTabs
{
    protected array $_tabs = [];

    public function renderingWithTabs(View $view): void
    {
        $this->_tabs = $this->getTabs();

        event('flux-core.livewire.rendering-with-tabs', $this);

        $view->with('tabs', collect($this->_tabs)->keyBy('component')->toArray());
    }

    abstract public function getTabs(): array;

    public function setTabsToRender(array $tabs): void
    {
        $this->_tabs = $tabs;
    }

    public function getTabsToRender(): array
    {
        return $this->_tabs;
    }
}
