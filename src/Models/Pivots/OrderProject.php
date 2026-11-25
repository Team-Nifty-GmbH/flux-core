<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProject extends FluxPivot
{
    protected $table = 'order_project';

    // Relations
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
