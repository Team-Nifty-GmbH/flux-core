<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Models\Pivots\ContactDiscount;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Model\SortableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;

class Discount extends FluxModel implements Sortable
{
    use HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes, SortableTrait;

    protected $hidden = [
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => Percentage::class,
            'discount_flat' => Money::class,
            'is_percentage' => 'boolean',
            'is_stackable' => 'boolean',
        ];
    }

    // Relations
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_discount')
            ->using(ContactDiscount::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    // Public methods
    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('model_type', $this->model_type)
            ->where('model_id', $this->model_id);
    }
}
