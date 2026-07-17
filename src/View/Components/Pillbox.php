<?php

namespace FluxErp\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Pillbox extends Component
{
    public function __construct(
        public ?string $label = null,
        public ?string $placeholder = null,
        public ?array $request = null,
        public int $lazy = 0,
    ) {}

    public function render(): View|Closure|string
    {
        return view('flux::components.pillbox');
    }
}
