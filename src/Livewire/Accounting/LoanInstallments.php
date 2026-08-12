<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\LoanInstallment;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class LoanInstallments extends BaseDataTable
{
    #[Modelable]
    public ?int $loanId = null;

    public array $columnLabels = [
        'sequence' => 'Sequence',
        'covered_amount' => 'Paid',
        'remaining' => 'Remaining',
        'status' => 'Status',
    ];

    public array $enabledCols = [
        'sequence',
        'due_date',
        'principal_amount',
        'interest_amount',
        'remaining',
        'covered_amount',
        'status',
    ];

    public bool $orderAsc = true;

    public string $orderBy = 'sequence';

    public array $formatters = [
        'due_date' => 'date',
        'principal_amount' => 'money',
        'interest_amount' => 'money',
        'remaining' => 'money',
        'covered_amount' => 'money',
        'status' => ['badge', [
            'Settled' => 'green',
            'Partially Paid' => 'amber',
            'Overdue' => 'red',
            'Open' => 'gray',
        ]],
    ];

    public array $sortable = [
        'sequence',
        'due_date',
        'principal_amount',
        'interest_amount',
    ];

    protected string $model = LoanInstallment::class;

    public function getBuilder(Builder $builder): Builder
    {
        $coverage = LoanInstallment::coverageSql();

        return $builder
            ->select('loan_installments.*')
            ->selectRaw($coverage . ' as covered_amount')
            ->selectRaw(
                '(select l.amount from loans l where l.id = loan_installments.loan_id)
                    - (select coalesce(sum(i2.principal_amount), 0)
                         from loan_installments i2
                        where i2.loan_id = loan_installments.loan_id
                          and i2.deleted_at is null
                          and i2.sequence <= loan_installments.sequence) as remaining'
            )
            ->selectRaw(
                'case
                    when loan_installments.is_paid = 1
                        or ' . $coverage . ' >= loan_installments.principal_amount + loan_installments.interest_amount
                        then ?
                    when loan_installments.due_date < curdate() then ?
                    when ' . $coverage . ' > 0 then ?
                    else ?
                end as status',
                ['Settled', 'Overdue', 'Partially Paid', 'Open']
            )
            ->where('loan_installments.loan_id', $this->loanId);
    }
}
