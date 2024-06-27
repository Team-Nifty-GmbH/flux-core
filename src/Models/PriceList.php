<?php

namespace FluxErp\Models;

use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PriceList extends Model
{
    use HasDefault, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'rounding_method_enum' => RoundingMethodEnum::class,
            'is_net' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function categoryDiscounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'category_price_list');
    }

    public function discount(): MorphOne
    {
        return $this->morphOne(Discount::class, 'model');
    }

    public function discountedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_price_list');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PriceList::class, 'parent_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}
