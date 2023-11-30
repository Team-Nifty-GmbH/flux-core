<?php

namespace FluxErp\Traits\Livewire;

use Illuminate\View\View;

trait WithTabs
{
    protected array $_tabs = [];

    abstract public function getTabs(): array;

    public function renderingWithTabs(View $view): void
    {
        $this->setTabsToRender($this->getTabs());

        event('tabs.rendering: ' . get_class($this), $this);

        $view->with('tabs', collect($this->_tabs)->keyBy('component')->toArray());
    }

    public function setTabsToRender(array $tabs): void
    {
        $this->_tabs = $tabs;
    }

    public function getTabsToRender(): array
    {
        return $this->_tabs;
    }
}
