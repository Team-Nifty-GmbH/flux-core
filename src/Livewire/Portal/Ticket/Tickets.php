<?php

namespace FluxErp\Livewire\Portal\Ticket;

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
        return view('flux::livewire.portal.ticket.tickets');
    }

    public function closeModal(): void
    {
        $this->showTicketModal = false;
        $this->skipRender();
    }

    public function show(): void
    {
        $this->dispatch('show')->to('portal.ticket.ticket-create');

        $this->showTicketModal = true;
        $this->skipRender();
    }
}
