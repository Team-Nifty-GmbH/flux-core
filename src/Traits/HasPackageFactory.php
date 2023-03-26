<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HasPackageFactory
{
    use HasFactory;

    protected static function newFactory()
    {
        return app('FluxErp\Database\Factories\\' . class_basename(static::class) . 'Factory');
    }
}
