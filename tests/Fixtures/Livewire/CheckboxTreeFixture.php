<?php

namespace FluxErp\Tests\Fixtures\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CheckboxTreeFixture extends Component
{
    public array $selected = [];

    public array $tree = [
        ['id' => 'alpha', 'label' => 'Alpha', 'children' => []],
        ['id' => 'beta', 'label' => 'Beta', 'children' => []],
        ['id' => 'gamma', 'label' => 'Gamma', 'children' => []],
    ];

    public function render(): View
    {
        return view()->file(__DIR__ . '/../views/checkbox-tree-fixture.blade.php');
    }
}
