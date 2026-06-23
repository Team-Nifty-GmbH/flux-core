<?php

namespace FluxErp\Models;

use FluxErp\Enums\MentionTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends FluxModel
{
    public $timestamps = false;

    protected $table = 'mentions';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'mention_type_enum' => MentionTypeEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function source(): MorphTo
    {
        return $this->morphTo('source', 'mention_source_type', 'mention_source_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo('target', 'mention_target_type', 'mention_target_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
