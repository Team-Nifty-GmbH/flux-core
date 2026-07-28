<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Loan;
use Illuminate\Database\Eloquent\Builder;

class LoanList extends BaseDataTable
{
    public array $columnLabels = [
        'contact.invoice_address.name' => 'Contact',
        'remaining_principal' => 'Remaining',
    ];

    public array $enabledCols = [
        'name',
        'contact.invoice_address.name',
        'amount',
        'remaining_principal',
        'number_of_installments',
        'starts_at',
    ];

    public array $formatters = [
        'amount' => 'money',
        'remaining_principal' => 'money',
        'starts_at' => 'date',
    ];

    public array $sortable = [
        'name',
        'amount',
        'starts_at',
    ];

    protected string $model = Loan::class;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->with('contact.invoiceAddress:id,contact_id,name')
            ->withSum(
                ['installments as remaining_principal' => fn (Builder $query) => $query->where('is_paid', false)],
                'principal_amount'
            );
    }
}
