<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    /**
     * What the accepted assignments cover, as a correlated subquery so the scopes
     * stay usable in WHERE and therefore sortable and countable in data tables.
     *
     * The assignments of a repayment are negative and a returned direct debit comes
     * back positive, so the sum is taken first and the sign dropped afterwards.
     * Summing absolutes would let a return add up instead of cancelling out.
     */
    protected static function coverageSql(): string
    {
        return '(SELECT ABS(COALESCE(SUM(amount), 0))
            FROM loan_installment_transaction lit
            WHERE lit.loan_installment_id = loan_installments.id
              AND lit.is_accepted = 1)';
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

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)
            ->using(LoanInstallmentTransaction::class)
            ->withPivot(['pivot_id', 'amount', 'note', 'is_accepted']);
    }

    // Public methods
    /**
     * What has to be paid for this installment, repayment plus interest.
     */
    public function getTotalAmount(): string
    {
        return bcround(bcadd((string) $this->principal_amount, (string) $this->interest_amount, 9), 2);
    }

    /**
     * The loan is read fresh on purpose. A cached relation carries the state of
     * the moment it was loaded, so a second save on the same installment would
     * compare against a stale original and skip the write.
     */
    public function recalculateLoan(): void
    {
        $this->loan()
            ->first()
            ?->calculateRemaining()
            ->calculateTotalInterest()
            ->calculateProgress()
            ->save();
    }

    // Scopes
    /**
     * An installment past its due date that nobody has covered yet.
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->unsettled()
            ->whereDate('due_date', '<', now()->toDateString());
    }

    public function scopeSettled(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->where('is_paid', true)
                ->orWhereRaw(static::coverageSql() . ' >= ROUND(principal_amount + interest_amount, 2)');
        });
    }

    public function scopeUnsettled(Builder $query): void
    {
        $query->where('is_paid', false)
            ->whereRaw(static::coverageSql() . ' < ROUND(principal_amount + interest_amount, 2)');
    }
}
