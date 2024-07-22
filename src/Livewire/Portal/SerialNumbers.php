<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Models\Address;
use FluxErp\Models\SerialNumber;
use FluxErp\Traits\Livewire\WithAddressAuth;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SerialNumbers extends Component
{
    use WithAddressAuth;

    public string $search = '';

    public array $addresses;

    // TODO: refactor to array
    public $serialNumbers;

    protected function getListeners(): array
    {
        $addresses = resolve_static(Address::class, 'query')
            ->where('contact_id', Auth::user()->contact_id)
            ->get();

        $listeners = [];
        foreach ($addresses as $address) {
            $channel = $address->broadcastChannel(false);
            $listeners = array_merge($listeners, [
                'echo-private:' . $channel . ',.SerialNumberCreated' => 'serialNumberCreatedEvent',
                'echo-private:' . $channel . ',.SerialNumberUpdated' => 'serialNumberUpdatedEvent',
                'echo-private:' . $channel . ',.SerialNumberDeleted' => 'serialNumberDeletedEvent',
            ]);
        }

        return $listeners;
    }

    public function boot(): void
    {
        $this->addresses = Auth::user()
            ->contact
            ->addresses
            ->pluck('id')
            ->toArray();

        $this->updatedSearch();
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.serial-numbers');
    }

    public function updatedSearch(): void
    {
        $this->serialNumbers = app(SerialNumber::class)->search($this->search)
            ->get()
            ->load('product');
    }

    public function serialNumberCreatedEvent(array $data): void {}

    public function serialNumberUpdatedEvent(array $data): void {}

    public function serialNumberDeletedEvent(array $data): void {}
}
