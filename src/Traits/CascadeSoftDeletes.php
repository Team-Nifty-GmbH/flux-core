<?php

namespace FluxErp\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait CascadeSoftDeletes
{
    use SoftDeletes;

    protected static function bootCascadeSoftDeletes(): void
    {
        static::deleting(function (Model $model): void {
            $model->runCascadingDeletes();
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

    protected function getActiveCascadingDeletes(): array
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return $this->{$relationship}()->exists();
        });
    }

    protected function getCascadingDeletes(): array
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
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

    protected function hasInvalidCascadingRelationships(): array
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return ! method_exists($this, $relationship) || ! $this->{$relationship}() instanceof Relation;
        });
    }

    protected function implementsSoftDeletes(): bool
    {
        return method_exists($this, 'runSoftDelete');
    }

    protected function runCascadingDeletes(): void
    {
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes($relationship);
        }
    }
}
