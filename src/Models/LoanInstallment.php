<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    /**
     * The columns the loan key figures are calculated from. Moving an
     * installment in time or in the order leaves them untouched.
     */
    protected const RECALCULATES_LOAN = [
        'loan_id',
        'principal_amount',
        'interest_amount',
        'is_paid',
    ];

    protected static function booted(): void
    {
        static::saved(function (LoanInstallment $loanInstallment): void {
            if (! $loanInstallment->wasRecentlyCreated
                && ! $loanInstallment->wasChanged(static::RECALCULATES_LOAN)
            ) {
                return;
            }

            $loanInstallment->recalculateLoan();
        });

        static::deleted(function (LoanInstallment $loanInstallment): void {
            $loanInstallment->recalculateLoan();
        });
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'principal_amount' => Money::class,
            'interest_amount' => Money::class,
            'is_paid' => 'boolean',
        ];
    }

    // Relations
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    // Public methods
    public function recalculateLoan(): void
    {
        $this->loan
            ?->calculateRemaining()
            ->calculateTotalInterest()
            ->calculateProgress()
            ->save();
    }
}
