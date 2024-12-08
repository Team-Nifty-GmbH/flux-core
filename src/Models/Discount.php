<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;

class Discount extends FluxModel implements Sortable
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes, SortableTrait;

    protected $hidden = [
        'pivot',
    ];

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => Percentage::class,
            'discount_flat' => Money::class,
            'is_percentage' => 'boolean',
        ];
    }

    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('model_type', $this->model_type)
            ->where('model_id', $this->model_id);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_discount');
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
