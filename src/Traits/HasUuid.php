<?php

namespace FluxErp\Traits;

use Ramsey\Uuid\Uuid;

trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });

        static::saving(function ($model) {
            $originalUuid = $model->getOriginal('uuid');
            if ($originalUuid !== $model->uuid && ! is_null($originalUuid)) {
                $model->uuid = $originalUuid;
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->mergeCasts([
            'uuid' => 'string',
        ]);
    }
}
