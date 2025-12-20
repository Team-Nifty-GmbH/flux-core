<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Communication;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Communicatable extends MorphPivot
{
    use ResolvesRelationsThroughContainer;

    public $timestamps = false;

    protected $table = 'communicatable';

    protected $primaryKey = 'pivot_id';

    protected $guarded = ['pivot_id'];

    public function communicatable(): MorphTo
    {
        return $this->morphTo('communicatable');
    }

    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }
}
