<?php

namespace FluxErp\Traits\Livewire;

use Illuminate\View\View;
use Illuminate\View\ViewException;

trait WithTabs
{
    protected array $_tabs = [];

    abstract public function getTabs(): array;

    /**
     * @throws ViewException
     */
    public function renderingWithTabs(?View $view = null): static
    {
        $this->setTabsToRender($this->getTabs());

        event('tabs.rendering: ' . get_class($this), $this);

        if ($view === null && ! app()->runningInConsole()) {
            throw new ViewException('View is null');
        }

        $view?->with('tabs', collect($this->_tabs)->keyBy('component')->toArray());

        return $this;
    }

    public function setTabsToRender(array $tabs): void
    {
        $this->_tabs = $tabs;
    }

    public function getTabsToRender(): array
    {
        return $this->_tabs;
    }

    public function mergeTabsToRender(array $tabs): void
    {
        $this->_tabs = array_merge($this->_tabs, $tabs);
    }
}
