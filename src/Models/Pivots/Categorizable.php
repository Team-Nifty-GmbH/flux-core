<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Category;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Categorizable extends MorphPivot
{
    use ResolvesRelationsThroughContainer;

    public $timestamps = false;

    protected $table = 'categorizable';

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];

    // Relations
    /**
     * @return MorphTo<Model, $this>
     */
    public function categorizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
