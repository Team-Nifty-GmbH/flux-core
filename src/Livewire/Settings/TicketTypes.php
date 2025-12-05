<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\TicketTypeList;
use FluxErp\Livewire\Forms\TicketTypeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;

class TicketTypes extends TicketTypeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public TicketTypeForm $ticketTypeForm;

    protected ?string $includeBefore = 'flux::livewire.settings.ticket-types';
}
