<?php

namespace FluxErp\Models;

use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PriceList extends FluxModel
{
    use CacheModelQueries, HasDefault, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid,
        LogsActivity, SoftDeletes;

    public static function hasPermission(): bool
    {
        return false;
    }

    protected static function booted(): void
    {
        static::saving(
            function (Model $model): void {
                if ($model->isDirty('is_purchase')) {
                    if ($model->is_purchase) {
                        $model->setUpdatedDefault();
                    }
                }

                if (
                    ! $model->is_purchase
                    && static::query()->where('is_purchase', true)->doesntExist()
                ) {
                    $model->is_purchase = true;
                }
            }
        );
    }

    protected function casts(): array
    {
        return [
            'rounding_method_enum' => RoundingMethodEnum::class,
            'is_net' => 'boolean',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
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

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}
