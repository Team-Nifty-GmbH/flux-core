<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Communication;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Communicatable extends MorphPivot
{
    use ResolvesRelationsThroughContainer;

    protected $table = 'communicatable';

    protected $guarded = [
        'id',
    ];

    public $timestamps = false;

    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }

    public function communicatable(): MorphTo
    {
        return $this->morphTo('communicatable');
    }
}
