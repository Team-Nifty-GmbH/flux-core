<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Task;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class TaskUser extends FluxPivot
{
    protected $table = 'task_user';

    // Relations
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function broadcastOn(string $event): array
    {
        return array_merge(
            Arr::wrap(parent::broadcastOn($event)),
            $this->userBroadcastChannels([$this->user_id])
        );
    }
}
