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

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contact->id);
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('plus')
                ->color('primary')
                ->label(__('New order'))
                ->wireClick('createOrder'),
        ];
    }

    #[Renderless]
    public function createOrder(): void
    {
        $this->order->reset();
        $this->order->contact_id = $this->contact->id;
        $contactId = $this->contact->id;

        $this->js(<<<JS
            updateContactId($contactId);
            \$openModal('create-order');
        JS);
    }
}
