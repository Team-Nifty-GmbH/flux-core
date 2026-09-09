<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class LoanPayments extends BaseDataTable
{
    #[Modelable]
    public ?int $loanId = null;

    public array $columnLabels = [
        'loan_installment.sequence' => 'Sequence',
        'transaction.booking_date' => 'Booking Date',
        'transaction.purpose' => 'Purpose',
        'is_accepted' => 'Accepted',
    ];

    public array $enabledCols = [
        'transaction.booking_date',
        'loan_installment.sequence',
        'transaction.purpose',
        'note',
        'amount',
        'is_accepted',
    ];

    public array $formatters = [
        'transaction.booking_date' => 'date',
        'amount' => 'money',
        'is_accepted' => 'boolean',
    ];

    #[Locked]
    public ?string $modelKeyName = 'pivot_id';

    public array $sortable = [
        'transaction.booking_date',
        'amount',
    ];

    protected string $model = LoanInstallmentTransaction::class;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder
            ->with(['loanInstallment:id,sequence', 'transaction:id,booking_date,purpose'])
            ->whereHas(
                'loanInstallment',
                fn (Builder $query) => $query->where('loan_id', $this->loanId)
            );
    }
}
