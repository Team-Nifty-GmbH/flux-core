<?php

namespace FluxErp\Http\Livewire\Portal\Ticket;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Tickets extends Component
{
    public bool $showTicketModal = false;

    protected $listeners = [
        'closeModal',
    ];

    public function render(): View
    {
        return view('flux::livewire.portal.ticket.tickets')->layout('flux::components.layouts.portal');
    }

    public function show(): void
    {
        $this->emitTo('portal.ticket.ticket-create', 'show');

        $this->showTicketModal = true;
        $this->skipRender();
    }

    public function closeModal(): void
    {
        $this->showTicketModal = false;
        $this->skipRender();
    }
}
