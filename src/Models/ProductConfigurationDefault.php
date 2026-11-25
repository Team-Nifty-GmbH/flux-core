<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasUserModification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductConfigurationDefault extends FluxModel
{
    use HasUserModification;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productConfigurationGroupItem(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationGroupItem::class);
    }
}
