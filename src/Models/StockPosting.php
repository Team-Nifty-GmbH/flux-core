<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class StockPosting extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockPosting $stockPosting) {
            Cache::lock('stock-posting-' . $stockPosting->warehouse_id . '-' . $stockPosting->product_id, 10)
                ->block(5, function () use ($stockPosting) {
                    $latestPosting = resolve_static(StockPosting::class, 'query')
                        ->where('warehouse_id', '=', $stockPosting->warehouse_id)
                        ->where('product_id', '=', $stockPosting->product_id)
                        ->latest('id')
                        ->first();

                    $stockPosting->stock = ($latestPosting->stock ?? 0) + $stockPosting->posting;
                });
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
