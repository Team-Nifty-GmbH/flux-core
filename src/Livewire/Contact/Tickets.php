<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class Tickets extends TicketList
{
    #[Modelable]
    public int $contactId;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereHasMorph(
            'authenticatable',
            Address::class,
            function ($query) {
                return $query->where('contact_id', $this->contactId);
            }
        );
    }
}
