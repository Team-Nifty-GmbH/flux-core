<?php

namespace FluxErp\Traits;

use FluxErp\Models\RecordOrigin;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOrigin
{
    public static function getOriginModelType(): string
    {
        return morph_alias(static::class);
    }

    public function origin(): BelongsTo
    {
        return $this
            ->belongsTo(RecordOrigin::class, 'origin_id')
            ->where('model_type', static::getOriginModelType());
    }
}
