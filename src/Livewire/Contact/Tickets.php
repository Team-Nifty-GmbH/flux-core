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

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereHasMorph(
            'authenticatable',
            app(Address::class)->getMorphClass(),
            fn ($query) => $query->where('contact_id', $this->contactId)
        );
    }

    public function getTableActions(): array
    {
        return [];
    }
}
