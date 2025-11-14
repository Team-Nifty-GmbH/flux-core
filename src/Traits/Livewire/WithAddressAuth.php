<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Models\Tenant;
use Illuminate\Support\Facades\Auth;

trait WithAddressAuth
{
    use EnsureUsedInLivewire;

    public array $customerTenant = [];

    public function mountWithAddressAuth(): void
    {
        $this->customerTenant = resolve_static(Tenant::class, 'query')
            ->whereKey(Auth::user()->contact->tenant_id)
            ?->first()
            ->toArray();
    }
}
