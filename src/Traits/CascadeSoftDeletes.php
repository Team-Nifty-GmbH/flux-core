<?php

namespace FluxErp\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;
use Illuminate\Support\Arr;

trait CascadeSoftDeletes
{
    use SoftDeletes;

    protected static function bootCascadeSoftDeletes(): void
    {
        static::deleting(function (Model $model): void {
            $model->runCascadingDeletes();
        });

        static::restoring(function (Model $model): void {
            $model->runCascadingRestores();
        });
    }

    protected function cascadeRestores(string $relationship): void
    {
        $closure = function (Model $model): void {
            isset($model->pivot) ? $model->pivot->restore() : $model->restore();
        };

        $this->handleSoftDeletedRecords($relationship, $closure);
    }

    protected function cascadeSoftDeletes(string $relationship): void
    {
        $delete = $this->forceDeleting ? 'forceDelete' : 'delete';

        $closure = function (Model $model) use ($delete): void {
            isset($model->pivot) ? $model->pivot->{$delete}() : $model->{$delete}();
        };

        $this->handleRecords($relationship, $closure);
    }

    protected function getActiveCascadingDeletes(): array
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return $this->{$relationship}()->exists();
        });
    }

    protected function getActiveCascadingRestores(): array
    {
        return array_filter($this->getCascadingRestores(), function ($relationship) {
            /** @var Relation $relation */
            $relation = $this->{$relationship}();

            return in_array(BaseSoftDeletes::class, class_uses_recursive($relation->getRelated()))
                && $relation->onlyTrashed()->exists();
        });
    }

    protected function getCascadingDeletes(): array
    {
        return property_exists($this, 'cascadeDeletes') && $this->cascadeDeletes
            ? Arr::wrap($this->cascadeDeletes)
            : [];
    }

    protected function getCascadingRestores(): array
    {
        return property_exists($this, 'cascadeRestores') && $this->cascadeRestores
            ? Arr::wrap($this->cascadeRestores)
            : $this->getCascadingDeletes();
    }

    protected function handleRecords(string $relationship, Closure $closure): void
    {
        $fetchMethod = $this->fetchMethod ?? 'get';

        if ($fetchMethod == 'chunk') {
            $this->{$relationship}()->chunk($this->chunkSize ?? 500, $closure);
        } else {
            foreach ($this->{$relationship}()->$fetchMethod() as $model) {
                $closure($model);
            }
        }
    }

    protected function handleSoftDeletedRecords(string $relationship, Closure $closure): void
    {
        $fetchMethod = $this->fetchMethod ?? 'get';

        if ($fetchMethod == 'chunk') {
            $this->{$relationship}()->onlyTrashed()->chunk($this->chunkSize ?? 500, $closure);
        } else {
            foreach ($this->{$relationship}()->onlyTrashed()->$fetchMethod() as $model) {
                $closure($model);
            }
        }
    }

    protected function runCascadingDeletes(): void
    {
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes($relationship);
        }
    }

    protected function runCascadingRestores(): void
    {
        foreach ($this->getActiveCascadingRestores() as $relationship) {
            $this->cascadeRestores($relationship);
        }
    }
}
