<?php

namespace FluxErp\Traits\Livewire;

trait EnsureUsedInLivewire
{
    abstract public function render();

    abstract public function getFormObjects();

    abstract public function js($expression);

    abstract public function disableBackButtonCache();

    abstract public function enableBackButtonCache();

    abstract public function stream($to, $content, $replace = false);
}
