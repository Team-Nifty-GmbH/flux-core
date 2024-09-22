<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Discount extends Model
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $hidden = [
        'pivot',
    ];

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'discount' => $this->is_percentage ? Percentage::class : Money::class,
            'is_percentage' => 'boolean',
        ];
    }

    public static function booted(): void
    {
        static::retrieved(function (Discount $model) {
            // this ensures that the right cast for discount is used
            $model->is_percentage = (bool) $model->is_percentage;
            $discount = $model->discount;
            $model->reloadCasts();
            $model->discount = $discount;
        });

        static::saving(function (Discount $model) {
            // this ensures that the right cast for discount is used
            $model->is_percentage = (bool) $model->is_percentage;
            $discount = $model->discount;
            $model->reloadCasts();
            $model->discount = $discount;
        });
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_discount');
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function reloadCasts(): void
    {
        $this->initializeHasAttributes();
    }
}
