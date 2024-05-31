<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Models\Client;
use Illuminate\Support\Facades\Auth;

trait WithAddressAuth
{
    use EnsureUsedInLivewire;

    public array $customerClient = [];

    public function mountWithAddressAuth(): void
    {
        $this->customerClient = app(Client::class)->query()
            ->whereKey(Auth::user()->contact->client_id)
            ?->first()
            ->toArray();
    }
}
