<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Task;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskUser extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $guarded = [
        'id',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
