<?php

namespace FluxErp\Traits;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Lock;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

trait Lockable
{
    protected static function bootLockable(): void
    {
        static::addGlobalScope(function ($builder): void {
            $builder->with('lock');
        });

        static::saving(function ($model): void {
            if ($model->is_locked && Auth::user()->isNot($model?->lock->user)) {
                throw new HttpResponseException(
                    ResponseHelper::createResponseFromBase(
                        statusCode: 423,
                        data: ['locked' => ['model is locked by another user']]
                    )
                );
            }
        });

        static::deleting(function ($model): void {
            if ($model->is_locked && Auth::user()->isNot($model?->lock->user)) {
                throw new HttpResponseException(
                    ResponseHelper::createResponseFromBase(
                        statusCode: 423,
                        data: ['locked' => ['model is locked by another user']]
                    )
                );
            }
        });
    }

    public function getIsLockedAttribute(): bool
    {
        $lock = $this->lock;

        return $lock !== null && Auth::user() && Auth::user()->isNot($lock?->user);
    }

    public function initializeLockable(): void
    {
        // TODO: Currently disabled as the locks dont have any functionality.
        // $this->setAppends(array_merge($this->appends ?? [], ['is_locked']));
    }

    public function lock(): MorphOne
    {
        return $this->morphOne(Lock::class, 'model');
    }
}
