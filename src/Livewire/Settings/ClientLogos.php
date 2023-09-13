<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Models\Client;
use FluxErp\Services\MediaService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use WireUi\Traits\Actions;

class ClientLogos extends Component
{
    use Actions, WithFileUploads;

    public string|array $logo = [];

    public string|array $logoSmall = [];

    public ?string $logoImage = null;

    public ?string $logoSmallImage = null;

    public int $clientId = 0;

    protected $listeners = [
        'show',
        'save',
    ];

    /**
     * @return string[][]
     */
    public function getRules(): array
    {
        return [
            'logo.0' => [
                'sometimes',
                'required',
                'image',
                'max:1024',
            ],
            'logoSmall.0' => [
                'sometimes',
                'required',
                'image',
                'max:512',
            ],
        ];
    }

    public function render(): View
    {
        return view('flux::livewire.settings.client-logos');
    }

    public function show(int $id): void
    {
        $client = Client::query()
            ->whereKey($id)
            ->first();

        $this->clientId = $id;
        $this->logo = [];
        $this->logoSmall = [];

        $logo = $client?->getFirstMedia('logo');
        $logoSmall = $client?->getFirstMedia('logo_small');

        $this->logoImage = ! is_null($logo) ? $logo?->toHtml() : null;
        $this->logoSmallImage = ! is_null($logoSmall) ? $logoSmall?->toHtml() : null;
    }

    public function save(): void
    {
        $this->resetErrorBag();

        $this->validate();

        $mediaService = new MediaService();

        if ($this->logo) {
            $mediaService->upload([
                'collection_name' => 'logo',
                'model_type' => Client::class,
                'model_id' => $this->clientId,
                'media' => $this->logo[0],
            ]);
        }

        if ($this->logoSmall) {
            $mediaService->upload([
                'collection_name' => 'logo_small',
                'model_type' => Client::class,
                'model_id' => $this->clientId,
                'media' => $this->logoSmall[0],
            ]);
        }

        $this->cleanupOldUploads();

        $this->logo = [];
        $this->logoSmall = [];

        $this->notification()->success(__('Logo(s) uploaded'));

        $this->skipRender();
        $this->dispatch('closeLogosModal');
    }

    public function removeUpload(string $name): void
    {
        if ($name === 'logoSmall') {
            $this->logoSmall = [];
        } else {
            $this->logo = [];
        }
    }
}
