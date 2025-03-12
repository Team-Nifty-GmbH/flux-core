<?php

namespace FluxErp\Support\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class CachedBuilder extends Builder
{
    protected bool $withoutCache = false;

    public static function cacheKey(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return 'model-query-cache:' . str(resolve_static($class, 'class'))
            ->lower()
            ->replace('\\', '-');
    }

    public function get($columns = ['*'])
    {
        if (! method_exists($this->getModel(), 'getModelQueryCacheTtl') || $this->withoutCache) {
            return parent::get($columns);
        }

        // get the result for this specific query from the cache
        $modelQueryCacheResult = data_get(
            Cache::get(static::cacheKey($this->getModel())),
            $this->queryCacheKey($columns),
        );

        if ($modelQueryCacheResult) {
            return $modelQueryCacheResult;
        }

        // if the result is not in the cache, we'll fetch it from the database
        $result = parent::get($columns);

        // store the result in the cache
        Cache::put(
            static::cacheKey($this->getModel()),
            array_merge(
                Cache::get(static::cacheKey($this->getModel()), []),
                [
                    $this->queryCacheKey($columns) => $result,
                ],
            ),
            $this->getModel()->getModelQueryCacheTtl(),
        );

        return $result;
    }

    public function withoutCache(bool $withoutCache = true): static
    {
        $this->withoutCache = $withoutCache;

        return $this;
    }

    protected function queryCacheKey(array|string $columns): string
    {
        return md5($this->toSql() . serialize($this->getBindings()) . serialize($columns));
    }
}
