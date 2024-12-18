<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Category;
use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Categorizable extends MorphPivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    protected $table = 'categorizables';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categorizable(): MorphTo
    {
        return $this->morphTo();
    }
}
