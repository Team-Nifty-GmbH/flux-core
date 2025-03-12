<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Models\Address;
use FluxErp\Traits\Livewire\WithAddressAuth;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Files extends Component
{
    use WithAddressAuth, WithFileUploads;

    public array $attachments = [];

    public array $filterSlug = [];

    public array $serialNumbers = [];

    public array $slugs = [];

    public function mount(): void
    {
        $addresses = resolve_static(Address::class, 'query')
            ->where('contact_id', Auth::user()?->contact_id)
            ->with([
                'serialNumbers' => fn ($query) => $query->whereHas('media')
                    ->select(['serial_numbers.id', 'serial_numbers.serial_number']),
                'serialNumbers.product:id,name',
                'serialNumbers.media',
            ])
            ->get('id')
            ->toArray();

        $serialNumbers = [];
        foreach ($addresses as $address) {
            $serialNumbers = array_merge($serialNumbers, data_get($address, 'serial_numbers', []));
        }

        $this->serialNumbers = array_unique(array_filter($serialNumbers));
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.files');
    }

    public function updatedFilterSlug(): void
    {
        if (! $this->filterSlug) {
            $this->attachments = $this->serialNumber->media->toArray();
        } else {
            $this->attachments = $this->serialNumber
                ->media
                ->whereIn('custom_properties.slug', $this->filterSlug)
                ->toArray();
        }
    }
}
