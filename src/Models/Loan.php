<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Loan extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Filterable, HasFrontendAttributes, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid,
        InteractsWithMedia, SoftDeletes;

    protected ?string $detailRouteName = 'accounting.loans.id';

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
            'repayment_type_enum' => RepaymentTypeEnum::class,
            'installment_interval_enum' => InstallmentIntervalEnum::class,
            'allows_extra_repayments' => 'boolean',
            'extra_repayment_allowance_percentage' => 'decimal:10',
            'extra_repayment_allowance_amount' => Money::class,
            'installment_amount' => Money::class,
            'remaining' => Money::class,
            'total_interest' => Money::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function extraRepayments(): HasMany
    {
        return $this->hasMany(LoanExtraRepayment::class);
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Public methods
    public function extraRepaymentAllowance(): ?string
    {
        if (! $this->allows_extra_repayments) {
            return '0.00';
        }

        if (is_null($this->extra_repayment_allowance_percentage)
            && is_null($this->extra_repayment_allowance_amount)
        ) {
            return null;
        }

        return bcround(
            bcadd(
                bcmul(
                    (string) $this->amount,
                    (string) ($this->extra_repayment_allowance_percentage ?? 0),
                    10
                ),
                (string) ($this->extra_repayment_allowance_amount ?? 0),
                10
            ),
            2
        );
    }

    public function remainingExtraRepaymentAllowance(int $year): ?string
    {
        $allowance = $this->extraRepaymentAllowance();

        if (is_null($allowance)) {
            return null;
        }

        $used = $this->usedExtraRepayments($year);

        return bccomp($allowance, $used, 2) === 1
            ? bcsub($allowance, $used, 2)
            : '0.00';
    }

    public function usedExtraRepayments(int $year): string
    {
        return bcround(
            (string) $this->extraRepayments()
                ->whereYear('executed_at', $year)
                ->sum('amount'),
            2
        );
    }

    public function calculateProgress(): static
    {
        $this->progress = bccomp((string) $this->amount, '0', 10) === 1
            ? bcdiv(bcsub((string) $this->amount, (string) $this->remaining, 10), (string) $this->amount, 10)
            : 0;

        return $this;
    }

    public function calculateRemaining(): static
    {
        $this->remaining = bcround(
            (string) $this->installments()
                ->unsettled()
                ->sum('principal_amount'),
            2
        );

        return $this;
    }

    public function calculateTotalInterest(): static
    {
        $this->total_interest = bcround(
            (string) $this->installments()
                ->sum('interest_amount'),
            2
        );

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->number;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('contract')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->singleFile();
    }
}
