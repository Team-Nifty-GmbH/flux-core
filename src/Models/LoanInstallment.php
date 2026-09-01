<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Scopes\LoanInstallmentInheritsLoanTenantScope;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class LoanInstallment extends FluxModel implements InteractsWithDataTables
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected const RECALCULATES_LOAN = [
        'loan_id',
        'principal_amount',
        'interest_amount',
        'is_paid',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(app(LoanInstallmentInheritsLoanTenantScope::class));

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

    public static function coverageSql(): string
    {
        return '(SELECT GREATEST(-COALESCE(SUM(amount), 0), 0)
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
    /**
     * @return BelongsTo<Loan, $this>
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * @return BelongsToMany<Transaction, $this>
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)
            ->using(LoanInstallmentTransaction::class)
            ->withPivot(['pivot_id', 'amount', 'note', 'is_accepted']);
    }

    // Public methods
    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return __('Due Date') . ' ' . $this->due_date?->locale(app()->getLocale())->isoFormat('L');
    }

    public function getLabel(): ?string
    {
        return trim(($this->loan?->name ?? '') . ' ' . __('Sequence') . ' ' . $this->sequence);
    }

    public function getUrl(): ?string
    {
        return $this->loan?->getUrl();
    }

    public function getTotalAmount(): string
    {
        return bcround(bcadd((string) $this->principal_amount, (string) $this->interest_amount, 9), 2);
    }

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
    protected function scopeOverdue(Builder $query): void
    {
        $query->unsettled()
            ->whereDate('due_date', '<', now()->toDateString());
    }

    protected function scopeCovered(Builder $query): void
    {
        $query->whereRaw(static::coverageSql() . ' > 0');
    }

    protected function scopeSettled(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->where('is_paid', true)
                ->orWhereRaw(static::coverageSql() . ' >= ROUND(principal_amount + interest_amount, 2)');
        });
    }

    protected function scopeUnsettled(Builder $query): void
    {
        $query->where('is_paid', false)
            ->whereRaw(static::coverageSql() . ' < ROUND(principal_amount + interest_amount, 2)');
    }
}
