<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Category;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Categorizable extends MorphPivot
{
    use ResolvesRelationsThroughContainer;

    protected $table = 'categorizables';

    public $incrementing = false;

    public $timestamps = false;

    public function categorizable(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
