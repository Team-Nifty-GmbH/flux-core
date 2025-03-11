<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Ticket;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithAddressAuth;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Livewire\Component;

class Service extends Component
{
    use Actions, WithAddressAuth, WithFileUploads;

    public $attachments = [];

    public array $contactData = [];

    public string $modelType = Ticket::class;

    public array $serialNumber;

    public array $ticket;

    protected $listeners = [
        'updateFilesArray',
        'removeUpload',
    ];

    public function mount($serialNumberId = null): void
    {
        $ticket = app(Ticket::class, [
            'attributes' => [
                'authenticatable_type' => Auth::user()->getMorphClass(),
                'authenticatable_id' => Auth::user()->id,
            ],
        ]);

        $this->ticket = $ticket->toArray();

        $this->contactData = Auth::user()->toArray();

        if ($serialNumberId) {
            $this->serialNumber = resolve_static(SerialNumber::class, 'query')
                ->whereKey($serialNumberId)
                ->firstOrFail()
                ->toArray();
            $this->contactData['serial_number'] = $this->serialNumber['serial_number'];

            $this->ticket['model_type'] = morph_alias(SerialNumber::class);
            $this->ticket['model_id'] = $this->serialNumber['id'];
        }
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.service');
    }

    public function save(): bool
    {
        try {
            $ticket = CreateTicket::make($this->ticket)
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        try {
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id, app(Ticket::class)->getMorphClass());
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Ticket created…'))->send();
        Event::dispatch('customerTicket.created', $ticket);

        $this->redirect(route('portal.dashboard'), true);

        return true;
    }

    public function updateFilesArray(): void
    {
        $this->filesArray = array_map(fn ($item) => $item->getClientOriginalName(), $this->attachments);

        $this->skipRender();
    }
}
