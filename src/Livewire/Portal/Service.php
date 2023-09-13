<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Ticket;
use FluxErp\Traits\Livewire\WithAddressAuth;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Livewire\Component;
use Livewire\Redirector;
use WireUi\Traits\Actions;

class Service extends Component
{
    use Actions, WithAddressAuth, WithFileUploads;

    public $attachments = [];

    public array $ticket;

    public array $serialNumber;

    public array $contactData = [];

    public string $modelType = Ticket::class;

    public function boot(): void
    {
        $ticket = new Ticket([
            'authenticatable_type' => get_class(Auth::user()),
            'authenticatable_id' => Auth::user()->id,
        ]);

        $this->ticket = $ticket->toArray();

        $this->contactData = Auth::user()->toArray();
    }

    public function mount($serialNumberId = null): void
    {
        if ($serialNumberId) {
            $this->serialNumber = SerialNumber::query()->whereKey($serialNumberId)->first()->toArray();
            $this->contactData['serial_number'] = $this->serialNumber['serial_number'];

            $this->ticket['model_type'] = SerialNumber::class;
            $this->ticket['model_id'] = $this->serialNumber['id'];
        }
    }

    public function render(): mixed
    {
        return view('flux::livewire.portal.service')
            ->layout('flux::components.layouts.portal');
    }

    public function save(): false|Redirector|RedirectResponse
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
            $this->saveFileUploadsToMediaLibrary('attachments', $ticket->id);
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Ticket created…'));
        Event::dispatch('customerTicket.created', $ticket);

        return redirect()->route('portal.dashboard');
    }
}
