<?php

namespace FluxErp\Tests\Fixtures\Livewire;

use Livewire\Component;

class TabsFixtureChild extends Component
{
    public $modelId = null;

    public function render(): string
    {
        return '<div>Tab child content</div>';
    }
}
