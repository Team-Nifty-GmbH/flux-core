<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Lock extends FluxModel
{
    use HasPackageFactory, HasUserModification, LogsActivity;

    protected $with = [
        'user',
    ];

    protected static function booted(): void
    {
        static::saving(function ($model): void {
            $model->authenticatable_type = Auth::user()->getMorphClass();
            $model->authenticatable_id = Auth::user()->getAuthIdentifier();
        });
    }

    public function user(): MorphTo
    {
        return $this->morphTo('authenticatable');
    }
}
