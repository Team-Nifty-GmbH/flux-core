<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Target;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetUser extends FluxPivot
{
    protected $primaryKey = 'pivot_id';

    protected $table = 'target_user';

    protected function casts(): array
    {
        return [
            'is_percentage' => 'boolean',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
