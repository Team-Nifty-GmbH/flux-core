<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttributeTranslation extends FluxModel
{
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
