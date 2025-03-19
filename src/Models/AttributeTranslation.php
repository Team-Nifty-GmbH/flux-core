<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttributeTranslation extends FluxModel
{
    use HasUserModification, SoftDeletes;

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
