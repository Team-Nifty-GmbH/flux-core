<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Traits\Livewire\WithAddressAuth;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Files extends Component
{
    use WithAddressAuth, WithFileUploads;

    public array $serialNumbers = [];

    public array $attachments = [];

    public array $slugs = [];

    public array $filterSlug = [];

    public function mount(): void
    {
        $this->serialNumbers = Auth::user()
            ->contact
            ->serialNumbers()
            ->whereHas('media')
            ->with(['product', 'media'])
            ->get()
            ->toArray();
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.files')
            ->layout('flux::components.layouts.portal');
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
