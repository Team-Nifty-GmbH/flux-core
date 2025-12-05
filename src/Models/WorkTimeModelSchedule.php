<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTimeModelSchedule extends FluxModel
{
    use HasUserModification, HasUuid;

    public function workTimeModel(): BelongsTo
    {
        return $this->belongsTo(WorkTimeModel::class);
    }
}
