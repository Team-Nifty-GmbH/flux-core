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

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'categorizables';

    public function categorizable(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
