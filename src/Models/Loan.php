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
    /**
     * The repaid share of the loan, between 0 and 1.
     */
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

    /**
     * The interest over the whole term. The schedule is locked once the loan
     * exists, so this only moves when an installment is added or removed.
     */
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
