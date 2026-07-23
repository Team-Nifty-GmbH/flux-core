<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends FluxModel
{
    use Filterable, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
            'installment_amount' => Money::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
            'repayment_type_enum' => RepaymentTypeEnum::class,
        ];
    }

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class);
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Attributes
    protected function remaining(): Attribute
    {
        return Attribute::get(
            fn (): string => bcadd(
                (string) $this->installments()
                    ->where('is_paid', false)
                    ->sum('principal_amount'),
                '0',
                2
            )
        );
    }
}
