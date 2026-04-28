<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventSubscription extends FluxModel
{
    use Filterable, HasPackageFactory;

    protected function casts(): array
    {
        return [
            'is_broadcast' => 'boolean',
            'is_notifiable' => 'boolean',
        ];
    }

    // Relations
    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }
}
