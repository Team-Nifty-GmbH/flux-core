<?php

namespace FluxErp\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected function cascadeSoftDeletes(string $relationship): void
    {
        $delete = $this->forceDeleting ? 'forceDelete' : 'delete';

        $closure = function (Model $model) use ($delete): void {
            isset($model->pivot) ? $model->pivot->{$delete}() : $model->{$delete}();
        };

        $this->handleRecords($relationship, $closure);
    }

    protected function cascadeSoftRestores(string $relationship): void
    {
        $closure = function (Model $model): void {
            if (isset($model->pivot)) {
                if ($this->modelImplementsSoftDeletes($model->pivot)) {
                    $model->pivot->restore();
                }
            } else {
                if ($this->modelImplementsSoftDeletes($model) && $model->trashed()) {
                    $model->restore();
                }
            }
        };

        $this->handleRecordsWithTrashed($relationship, $closure);
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
            return $this->{$relationship}()->onlyTrashed()->exists();
        });
    }

    protected function getCascadingDeletes(): array
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
    }

    protected function getCascadingRestores(): array
    {
        return isset($this->cascadeRestores) ? (array) $this->cascadeRestores : $this->getCascadingDeletes();
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

    protected function handleRecordsWithTrashed(string $relationship, Closure $closure): void
    {
        $fetchMethod = $this->fetchMethod ?? 'get';

        if ($fetchMethod == 'chunk') {
            $this->{$relationship}()->withTrashed()->chunk($this->chunkSize ?? 500, $closure);
        } else {
            foreach ($this->{$relationship}()->withTrashed()->$fetchMethod() as $model) {
                $closure($model);
            }
        }
    }

    protected function hasInvalidCascadingRelationships(): array
    {
        $invalidDeletes = array_filter($this->getCascadingDeletes(), function ($relationship) {
            return ! method_exists($this, $relationship) || ! $this->{$relationship}() instanceof Relation;
        });

        $invalidRestores = array_filter($this->getCascadingRestores(), function ($relationship) {
            return ! method_exists($this, $relationship) || ! $this->{$relationship}() instanceof Relation;
        });

        return array_unique(array_merge($invalidDeletes, $invalidRestores));
    }

    protected function implementsSoftDeletes(): bool
    {
        return method_exists($this, 'runSoftDelete');
    }

    protected function modelImplementsSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
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
            $this->cascadeSoftRestores($relationship);
        }
    }
}
