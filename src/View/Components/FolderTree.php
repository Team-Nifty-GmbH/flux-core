<?php

namespace FluxErp\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FolderTree extends Component
{
    public array $tree;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $tree)
    {
        $this->tree = $tree;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('flux::components.folder-tree');
    }
}
