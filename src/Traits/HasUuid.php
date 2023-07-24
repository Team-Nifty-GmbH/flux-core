<?php

namespace FluxErp\Traits;

use FluxErp\Models\Ticket;
use Ramsey\Uuid\Uuid;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = Uuid::uuid4()->toString();
                if ($model instanceof Ticket) {
                    dd($model);
                }
            }
        });

        static::saving(function ($model) {
            $originalUuid = $model->getOriginal('uuid');
            if ($originalUuid !== $model->uuid) {
                $model->uuid = $originalUuid;
            }
        });
    }
}
