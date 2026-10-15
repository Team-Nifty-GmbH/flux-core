<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductConfigurationGroupItem extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity;

    protected function casts(): array
    {
        return [
            'surcharge' => Money::class,
            'max_amount' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productConfigurationDefaults(): HasMany
    {
        return $this->hasMany(ProductConfigurationDefault::class);
    }

    public function productConfigurationGroup(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationGroup::class);
    }

    public function productConfigurationRules(): HasMany
    {
        return $this->hasMany(ProductConfigurationRule::class);
    }
}
