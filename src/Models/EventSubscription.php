<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventSubscription extends FluxModel
{
    use Filterable, HasPackageFactory;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_broadcast' => 'boolean',
            'is_notifiable' => 'boolean',
        ];
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }
}
