<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Forms\LeadForm;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;

class Orders extends OrderList
{
    #[Modelable]
    public LeadForm $leadForm;

    #[Renderless]
    public function create(): void
    {
        $this->order->contact_id = resolve_static(Contact::class, 'query')
            ->whereHas('addresses', fn (Builder $builder) => $builder->whereKey($this->leadForm->address_id))
            ->value('id');
        $this->order->lead_id = $this->leadForm->id;

        $contactId = $this->order->contact_id;
        $this->js(<<<JS
            \$tallstackuiSelect('invoice-address-id').mergeRequestParams({
                where: [['contact_id', '=', $contactId]],
            })
            \$tallstackuiSelect('delivery-address-id').mergeRequestParams({
                where: [['contact_id', '=', $contactId]],
            })
        JS);

        $this->fetchContactData();
        $this->order->agent_id = $this->leadForm->user_id;

        parent::create();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return parent::getBuilder($builder)->where('lead_id', $this->leadForm->id);
    }
}
