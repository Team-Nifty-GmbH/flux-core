<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class StockPosting extends FluxModel
{
    use Filterable, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (StockPosting $stockPosting): void {
            Cache::lock('stock-posting-' . $stockPosting->warehouse_id . '-' . $stockPosting->product_id, 10)
                ->block(5, function () use ($stockPosting): void {
                    $latestPosting = resolve_static(StockPosting::class, 'query')
                        ->where('warehouse_id', '=', $stockPosting->warehouse_id)
                        ->where('product_id', '=', $stockPosting->product_id)
                        ->latest('id')
                        ->first();

                    $stockPosting->stock = ($latestPosting->stock ?? 0) + $stockPosting->posting;

                    if ($stockPosting->posting > 0) {
                        $stockPosting->remaining_stock = $stockPosting->posting;
                    }
                });
        });
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // Relations
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class, 'lot_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function warehouseBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'warehouse_bin_id');
    }

    // Scopes
    /**
     * Layers whose lot already expired stay in the result on purpose: they hold stock that still
     * needs handling, so a shelf-life view that hid them would hide the most urgent cases. Only the
     * upper end of the window is therefore bounded.
     *
     * A best-before date is a calendar date, so the window is anchored in the display timezone
     * rather than in UTC, which would move the boundary by a day for anyone east or west of it.
     */
    public function scopeExpiringWithin(Builder $query, int $days): void
    {
        if ($days < 1) {
            throw new InvalidArgumentException('The shelf life window must span at least one day');
        }

        $query->where('remaining_stock', '>', 0)
            ->whereHas('lot', fn (Builder $query) => $query
                ->whereNotNull('expires_at')
                ->where(
                    'expires_at',
                    '<=',
                    today(config('flux.display_timezone') ?? config('app.timezone'))->addDays($days)
                )
            );
    }
}
