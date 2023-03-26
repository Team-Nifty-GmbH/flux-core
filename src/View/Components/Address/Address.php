<?php

namespace FluxErp\View\Components\Address;

use FluxErp\Models\Country;
use FluxErp\Models\Language;
use Illuminate\View\Component;

class Address extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('flux::components.address.address', [
            'languages' => Language::all(['id', 'name'])->toArray(),
            'countries' => Country::all(['id', 'name'])->toArray(),
        ]);
    }
}
