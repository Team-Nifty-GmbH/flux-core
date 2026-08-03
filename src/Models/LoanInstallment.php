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

    protected static function booted(): void
    {
        static::saved(function (LoanInstallment $loanInstallment): void {
            $loanInstallment->loan
                ?->calculateRemaining()
                ->calculateTotalInterest()
                ->calculateProgress()
                ->save();
        });

        static::deleted(function (LoanInstallment $loanInstallment): void {
            $loanInstallment->loan
                ?->calculateRemaining()
                ->calculateTotalInterest()
                ->calculateProgress()
                ->save();
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
}
