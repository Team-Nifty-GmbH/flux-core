<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Models\Scopes\FixedAssetDepreciationInheritsFixedAssetTenantScope;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FixedAssetDepreciation extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(app(FixedAssetDepreciationInheritsFixedAssetTenantScope::class));
    }

    public static function isMorphType(?string $type): bool
    {
        return filled($type) && is_a(morphed_model($type) ?? $type, FixedAssetDepreciation::class, true);
    }

    public static function externallyDepreciated(Builder $query): Builder
    {
        return $query->whereColumn(
            'fixed_assets.externally_depreciated_until',
            '>=',
            'fixed_asset_depreciations.booking_date'
        );
    }

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'amount' => Money::class,
            'book_value_after' => Money::class,
        ];
    }

    // Relations
    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function ledgerBookings(): MorphMany
    {
        return $this->morphMany(LedgerBooking::class, 'source');
    }

    // Scopes
    protected function scopeBooked(Builder $query): void
    {
        $query->whereHas('ledgerBookings');
    }

    protected function scopeDepreciated(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->whereHas('ledgerBookings')
                ->orWhereHas('fixedAsset', fn (Builder $query): Builder => static::externallyDepreciated($query));
        });
    }

    protected function scopeUndepreciated(Builder $query): void
    {
        $query->whereDoesntHave('ledgerBookings')
            ->whereDoesntHave('fixedAsset', fn (Builder $query): Builder => static::externallyDepreciated($query));
    }
}
