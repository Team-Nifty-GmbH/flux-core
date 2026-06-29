<?php

namespace FluxErp\Models;

use FluxErp\Enums\MentionTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends FluxModel
{
    protected function casts(): array
    {
        return [
            'mention_type_enum' => MentionTypeEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations
    public function mentionSource(): MorphTo
    {
        return $this->morphTo();
    }

    public function mentionTarget(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
