<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class Leads extends LeadList
{
    #[Modelable]
    public ?int $contactId = null;

    public function edit(string|int|null $id = null): void
    {
        parent::edit($id);

        $this->leadForm->address_id ??= resolve_static(Contact::class, 'query')
            ->whereKey($this->contactId)
            ->value('main_address_id');
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereRelation('address.contact', 'id', $this->contactId);
    }
}
