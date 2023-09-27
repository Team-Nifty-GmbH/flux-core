<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Contact;
use FluxErp\Models\Discount;

class ContactAllDiscountsList extends DiscountList
{
    protected string $model = Discount::class;

    public bool $isFilterable = false;

    public int $contactId;

    public function loadData(): void
    {
        $this->initialized = true;

        $this->setData(Contact::query()
            ->whereKey($this->contactId)
            ->firstOrFail()
            ->getAllDiscounts()
            ->each(fn (Discount $discount) => $discount->load('model'))
            ->map(fn (Discount $discount) => $this->itemToArray($discount))
            ->toArray());
    }
}
