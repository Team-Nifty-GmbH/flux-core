<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Target;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetUser extends FluxPivot
{
    protected $table = 'target_user';

    protected function casts(): array
    {
        return [
            'is_percentage' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<Target, $this>
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
