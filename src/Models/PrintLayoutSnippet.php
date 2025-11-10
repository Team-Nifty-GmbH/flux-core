<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintLayoutSnippet extends FluxModel
{
    use HasUserModification;

    public function printLayout(): BelongsTo
    {
        return $this->belongsTo(PrintLayout::class);
    }
}
