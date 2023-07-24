<?php

namespace FluxErp\Traits;

use Ramsey\Uuid\Uuid;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = Uuid::uuid4()->toString();
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
