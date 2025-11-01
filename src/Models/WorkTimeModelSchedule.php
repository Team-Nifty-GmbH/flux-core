<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTimeModelSchedule extends FluxModel
{
    use HasUserModification, HasUuid;

    public function workTimeModel(): BelongsTo
    {
        return $this->belongsTo(WorkTimeModel::class);
    }
}
