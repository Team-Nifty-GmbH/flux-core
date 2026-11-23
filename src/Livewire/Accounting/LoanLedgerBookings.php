<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\LedgerBookingList;
use FluxErp\Models\Loan;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class LoanLedgerBookings extends LedgerBookingList
{
    #[Modelable]
    public ?int $loanId = null;

    public array $enabledCols = [
        'booking_date',
        'debitLedgerAccount.name',
        'creditLedgerAccount.name',
        'amount',
        'booking_text',
    ];

    public function getBuilder(Builder $builder): Builder
    {
        $loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->loanId)
            ->first(['id', 'ledger_account_id']);

        if (! $loan) {
            return parent::getBuilder($builder)->whereRaw('1 = 0');
        }

        return parent::getBuilder($builder)
            ->where(function (Builder $query) use ($loan): void {
                $query->where('debit_ledger_account_id', $loan->ledger_account_id)
                    ->orWhere('credit_ledger_account_id', $loan->ledger_account_id)
                    ->orWhere(function (Builder $query) use ($loan): void {
                        $query->where('source_type', morph_alias(LoanInstallmentTransaction::class))
                            ->whereIn(
                                'source_id',
                                resolve_static(LoanInstallmentTransaction::class, 'query')
                                    ->select('pivot_id')
                                    ->whereIn(
                                        'loan_installment_id',
                                        $loan->installments()->select('id')
                                    )
                            );
                    });
            });
    }
}
