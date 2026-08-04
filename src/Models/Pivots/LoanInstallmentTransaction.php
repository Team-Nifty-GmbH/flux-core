<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Transaction;
use FluxErp\Traits\Model\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallmentTransaction extends FluxPivot
{
    use HasPackageFactory;

    public $timestamps = true;

    protected $table = 'loan_installment_transaction';

    protected static function booted(): void
    {
        static::saved(function (LoanInstallmentTransaction $loanInstallmentTransaction): void {
            $loanInstallmentTransaction->recalculate();
        });

        static::deleted(function (LoanInstallmentTransaction $loanInstallmentTransaction): void {
            $loanInstallmentTransaction->recalculate();
        });
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    public function loanInstallment(): BelongsTo
    {
        return $this->belongsTo(LoanInstallment::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * The installment decides the loan key figures, the transaction only counts
     * an assignment against its balance once it is accepted.
     */
    public function recalculate(): void
    {
        $this->loanInstallment?->recalculateLoan();

        if ($this->is_accepted) {
            $this->transaction?->calculateBalance()->save();
        }
    }
}
