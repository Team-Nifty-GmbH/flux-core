<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Lead\LeadList;
use Livewire\Attributes\Modelable;

class Leads extends LeadList
{
    #[Modelable]
    public ?int $contactId = null;

    public function edit(string|int|null $id = null): void
    {
        parent::edit($id);

        $this->leadForm->address_id ??= resolve_static(\FluxErp\Models\Contact::class, 'query')
            ->whereKey($this->contactId)
            ->value('main_address_id');
    }
}
