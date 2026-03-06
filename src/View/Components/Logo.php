<?php

namespace FluxErp\View\Components;

use Closure;
use FluxErp\Models\Tenant;
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
            $tenant = resolve_static(Tenant::class, 'query')
                ->whereKey(auth()->user()->getTenantId())
                ->first();

            $this->default = false;
            $this->logo = $tenant?->getFirstMedia('logo');
            $this->logoSmall = $tenant?->getFirstMedia('logo_small');
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
