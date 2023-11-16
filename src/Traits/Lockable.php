<?php

namespace FluxErp\Traits;

use FluxErp\Helpers\ResponseHelper;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Fluent;

trait Lockable
{
    protected static function bootLockable(): void
    {
        $lockCheck = function (Model $model) {
            if ($model->getHasLockAttribute() && auth()->user()->isNot($model->getLockedBy())) {
                throw new HttpResponseException(
                    ResponseHelper::createResponseFromBase(
                        statusCode: 423,
                        data: ['locked' => ['model is locked by ' . $model->getLockedBy()->name]]
                    )
                );
            }
        };

        static::saving($lockCheck);

        static::deleting($lockCheck);
    }

    public function initializeLockable(): void
    {
        $this->setAppends(array_merge($this->appends ?? [], ['has_lock']));
    }

    public function getLockName(): string
    {
        return 'lockable:' . static::class . ':' . $this->id;
    }

    public function getLockOwner(): ?string
    {
        return data_get(Session::get('locks'), $this->getLockName());
    }

    public function getLockedBy(): Authenticatable|Fluent|null
    {
        return Cache::get('cache_' . $this->getLockName());
    }

    public function lock(): bool
    {

        $lockDuration = config('session.lifetime') * 60;
        $lock = Cache::lock($this->getLockName(), $lockDuration);

        if ($hasLock = $lock->get()) {
            $currentLocks = Session::get('locks', []);
            Session::put(
                'locks',
                array_merge($currentLocks, [$this->getLockName() => $lock->owner()])
            );
            // Store all locks in the user locks cache
            Cache::put(
                'user-locks:' . auth()->user()?->getMorphClass() ?? 'system' . ':' . auth()->user()->id,
                array_merge(
                    Cache::get(
                        'user-locks:' . auth()->user()?->getMorphClass() ?? 'system' . ':' . auth()->user()->id,
                        []
                    ),
                    [$this->getLockName() => $lock->owner()]
                ),
            );
            Cache::put(
                'cache_' . $this->getLockName(),
                auth()->user() ?? new Fluent(['name' => 'system']),
                $lockDuration
            );
        }

        return $hasLock;
    }

    public function unlock(): bool
    {
        if ($unlocked = Cache::restoreLock($this->getLockName(), $this->getLockOwner())->release()) {
            $this->removeLockFromCache();
        }

        return $unlocked;
    }

    public function getHasLockAttribute(): bool
    {
        return Cache::has('cache_' . $this->getLockName());
    }

    public function getLockedByAttribute(): ?Authenticatable
    {
        return $this->getLockedBy();
    }

    public function forceUnlock(): void
    {
        Cache::lock($this->getLockName())->forceRelease();
        $this->removeLockFromCache();
    }

    private function removeLockFromCache(): void
    {
        Cache::forget('cache_' . $this->getLockName());

        $currentLocks = Session::get('locks', []);
        unset($currentLocks[$this->getLockName()]);
        Session::put(
            'locks',
            $currentLocks,
        );

        $userLocks = Cache::get(
            'user-locks:' . auth()->user()?->getMorphClass() ?? 'system' . ':' . auth()->user()->id,
            []
        );
        unset($userLocks[$this->getLockName()]);

        Cache::put(
            'user-locks:' . auth()->user()?->id ?? 'system',
            $userLocks,
        );
    }
}
