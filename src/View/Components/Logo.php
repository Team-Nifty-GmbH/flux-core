<?php

namespace FluxErp\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Logo extends Component
{
    public bool $default = true;

    public $logo;

    public $logoSmall;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (auth()->user() instanceof \FluxErp\Models\Address) {
            $this->default = false;
            $this->logo = auth()->user()->contact->tenant?->getFirstMedia('logo');
            $this->logoSmall = auth()->user()->contact->tenant?->getFirstMedia('logo_small');
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('flux::components.logo');
    }
}
