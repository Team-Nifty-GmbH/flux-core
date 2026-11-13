<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Enums\DepreciationMethodEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class FixedAsset extends FluxModel implements InteractsWithDataTables
{
    use Filterable, HasFrontendAttributes, HasPackageFactory, HasTenantAssignment, HasUserModification, HasUuid,
        SoftDeletes;

    protected ?string $detailRouteName = 'accounting.fixed-assets.id';

    protected function casts(): array
    {
        return [
            'acquired_at' => 'date',
            'depreciation_starts_at' => 'date',
            'externally_depreciated_until' => 'date',
            'acquisition_cost' => Money::class,
            'residual_value' => Money::class,
            'book_value' => Money::class,
            'depreciation_method_enum' => DepreciationMethodEnum::class,
            'useful_life_months' => 'integer',
        ];
    }

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function depreciationLedgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'depreciation_ledger_account_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(FixedAssetDepreciation::class);
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Public methods
    public function calculateBookValue(): static
    {
        $this->book_value = bcsub(
            (string) data_get($this->getAttributes(), 'acquisition_cost'),
            (string) $this->depreciations()
                ->depreciated()
                ->sum('amount'),
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

    public function hasBookedDepreciations(): bool
    {
        return $this->depreciations()
            ->booked()
            ->exists();
    }

    public function recalculateBookValue(): void
    {
        $this->calculateBookValue()
            ->save();
    }
}
