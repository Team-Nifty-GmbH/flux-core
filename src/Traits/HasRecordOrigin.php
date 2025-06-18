<?php

namespace FluxErp\Traits;

use FluxErp\Models\RecordOrigin;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRecordOrigin
{
    public function origin(): BelongsTo
    {
        return $this->belongsTo(RecordOrigin::class, 'record_origin_id');
    }
}
