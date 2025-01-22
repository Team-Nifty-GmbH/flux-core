<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Livewire\DataTables\DiscountList;
use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use Livewire\Attributes\Modelable;

class AllDiscounts extends DiscountList
{
    public bool $isFilterable = false;

    #[Modelable]
    public int $contactId;

    public function loadData(): void
    {
        $this->initialized = true;

        $this->setData(resolve_static(Contact::class, 'query')
            ->whereKey($this->contactId)
            ->firstOrFail()
            ->getAllDiscounts()
            ->each(fn (Discount $discount) => $discount->load('model'))
            ->map(fn (Discount $discount) => $this->itemToArray($discount))
            ->toArray());
    }
}
