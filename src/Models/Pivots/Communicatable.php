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

    public $timestamps = false;

    protected $guarded = [
        'id',
    ];

    protected $table = 'communicatable';

    public function communicatable(): MorphTo
    {
        return $this->morphTo('communicatable');
    }

    public function communication(): BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }
}
