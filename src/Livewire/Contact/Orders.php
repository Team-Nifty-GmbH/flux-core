<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Forms\ContactForm;
use FluxErp\Livewire\Order\OrderList;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Orders extends OrderList
{
    #[Modelable]
    public ContactForm $contact;

    protected ?string $includeBefore = 'flux::livewire.contact.orders';

    protected function getTableActions(): array
    {
        return array_merge(
            parent::getTableActions(),
            [
                DataTableButton::make()
                    ->icon('plus')
                    ->color('indigo')
                    ->text(__('New order'))
                    ->wireClick('createOrder'),
            ]
        );
    }

    #[Renderless]
    public function create(): void
    {
        $this->order->contact_id = $this->contact->id;
        $contactId = $this->contact->id;
        $this->js(<<<JS
            \$tallstackuiSelect('invoice-address-id').mergeRequestParams({
                where: [['contact_id', '=', $contactId]],
            })
            \$tallstackuiSelect('delivery-address-id').mergeRequestParams({
                where: [['contact_id', '=', $contactId]],
            })
        JS);
        $this->fetchContactData();

        parent::create();
    }

    #[Renderless]
    public function getCacheKey(): string
    {
        return parent::getCacheKey() . $this->contact->id;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contact->id);
    }
}
