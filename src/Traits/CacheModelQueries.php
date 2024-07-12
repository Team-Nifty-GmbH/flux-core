<?php

namespace FluxErp\Traits;

use FluxErp\Support\Database\CachedBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait CacheModelQueries
{
    public static function bootCacheModelQueries(): void
    {
        static::$builder = CachedBuilder::class;

        static::saved(fn (Model $model) => $model->flushModelQueryCache());
        static::deleted(fn (Model $model) => $model->flushModelQueryCache());
    }

    public function getModelQueryCacheTtl(): ?int
    {
        return 86400;
    }

    public function flushModelQueryCache(): void
    {
        Cache::forget(CachedBuilder::cacheKey(static::class));
    }
}
