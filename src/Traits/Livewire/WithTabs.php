<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Htmlables\TabButton;
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

    public function getTabButton(string $component): TabButton
    {
        $this->setTabsToRender($this->getTabs());

        // fire event to get tab buttons that are registered
        event('tabs.rendering: ' . get_class($this), $this);

        return collect($this->getTabsToRender())->keyBy('component')->toArray()[$component];
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
