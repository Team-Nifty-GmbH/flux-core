<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait HasDefault
{
    protected static string $defaultColumn = 'is_default';

    private bool $updatedDefault = false;

    protected static function bootHasDefault(): void
    {
        static::saving(
            function (Model $model) {
                if ($model->isDirty(static::$defaultColumn)) {
                    Cache::forget('default_' . morph_alias(static::class));

                    if ($model->{static::$defaultColumn}) {
                        $model->setUpdatedDefault();
                    }
                }

                // if a default column is given at least one model has to be default
                if (
                    ! $model->{static::$defaultColumn}
                    && static::query()->where(static::$defaultColumn, true)->doesntExist()
                ) {
                    $model->{static::$defaultColumn} = true;
                }
            }
        );

        static::saved(function (Model $model) {
            if ($model->getUpdatedDefault()) {
                static::query()
                    ->whereKeyNot($model->getKey())
                    ->where(static::$defaultColumn, true)
                    ->update([static::$defaultColumn => false]);
            }
        });

        static::deleted(function (Model $model) {
            if ($model->{static::$defaultColumn}) {
                $default = static::query()->first();
                if ($default) {
                    $default->{static::$defaultColumn} = true;
                    $default->save();
                }
            }
        });
    }

    public static function default(): ?static
    {
        return Cache::rememberForever(
            'default_' . morph_alias(static::class),
            fn () => static::query()->where(static::$defaultColumn, true)->first()
        );
    }

    public function setUpdatedDefault(bool $updated = true): void
    {
        $this->updatedDefault = $updated;
    }

    public function getUpdatedDefault(): bool
    {
        return $this->updatedDefault;
    }
}
