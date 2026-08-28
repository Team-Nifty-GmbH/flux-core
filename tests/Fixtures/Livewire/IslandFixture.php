<?php

namespace FluxErp\Tests\Fixtures\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class IslandFixture extends Component
{
    public ?string $choice = null;

    public function render(): View
    {
        return view()->file(__DIR__ . '/../views/island-fixture.blade.php');
    }

    public function repaint(): void
    {
        $this->renderIsland('island-fixture');
    }
}
