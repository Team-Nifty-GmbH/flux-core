<?php

namespace FluxErp\Http\Livewire\Portal;

use FluxErp\Traits\Livewire\WithAddressAuth;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

class FileList extends Component
{
    use WithFileUploads, WithAddressAuth;

    public array $attachments = [];

    public array $allAttachments = [];

    public array $slugs = [];

    public array $filterSlug = [];

    public function mount(array $attachments): void
    {
        $attachmentsWithSlug = [];
        foreach ($attachments as $attachment) {
            $attachment['slug'] = $attachment['custom_properties']['slug'] ?? null;
            $attachmentsWithSlug[] = $attachment;
        }

        $this->attachments = $attachmentsWithSlug;
        $this->allAttachments = $attachmentsWithSlug;
        $this->slugs = array_filter(
            array_values(
                array_unique(
                    Arr::pluck($this->attachments, 'custom_properties.slug')
                )
            )
        );
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.portal.file-list');
    }

    public function updatedFilterSlug(): void
    {
        if (! $this->filterSlug) {
            $this->attachments = $this->allAttachments;
        } else {
            $this->attachments = collect($this->allAttachments)
                ->whereIn('slug', $this->filterSlug)
                ->toArray();
        }
    }
}
