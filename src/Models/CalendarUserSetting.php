<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class CalendarUserSetting extends FluxModel
{
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->authenticatable_id = $model->authenticatable_id ?? Auth::user()->id;
            $model->authenticatable_type = $model->authenticatable_type ?? Auth::user()->getMorphClass();
        });
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }
}
