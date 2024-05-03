<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Client;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class ClientProduct extends Pivot
{
    use BroadcastsEvents;

    protected $table = 'client_product';

    public $timestamps = false;

    public $incrementing = true;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(ClientProduct::class, 'product_id', 'product_id');
    }
}
