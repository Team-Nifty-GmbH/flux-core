<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\Order\OrderList;
use Illuminate\Database\Eloquent\Builder;

class OutstandingItems extends OrderList
{
    public ?string $cacheKey = 'accounting.outstanding-items';

    public array $enabledCols = [
        'invoice_number',
        'invoice_date',
        'contact.customer_number',
        'address_invoice.name',
        'total_gross_price',
        'balance',
        'payment_reminder_current_level',
    ];

    protected function getBuilder(Builder $builder): Builder
    {
        return parent::getBuilder($builder)
            ->unpaid()
            ->revenue();
    }
}
