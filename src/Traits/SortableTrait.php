<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait as BaseSortableTrait;

trait SortableTrait
{
    use BaseSortableTrait {
        BaseSortableTrait::bootSortableTrait as bootParentSortableTrait;
    }

    private bool $isSorted = false;

    protected static function bootSortableTrait(): void
    {
        static::bootParentSortableTrait();

        static::saving(function (Model $model): void {
            $orderColumn = $model->determineOrderColumnName();

            if ($model->isDirty($orderColumn) && ! $model->getIsSorted() && $model->exists) {
                $newPosition = $model->$orderColumn ?? $model->getHighestOrderNumber() + 1;
                $model->$orderColumn = $model->getRawOriginal($orderColumn);

                $model->moveToPosition($newPosition);
            }
        });

        static::deleting(function (Model $model): void {
            $orderColumn = $model->determineOrderColumnName();

            if (! $model->hasAttribute($orderColumn)) {
                $model->$orderColumn = $model->newModelQuery()->whereKey($model->getKey())->value($orderColumn);
            }
        });

        static::deleted(function (Model $model): void {
            $orderColumn = $model->determineOrderColumnName();
            $orderValue = $model->$orderColumn;

            $model->buildSortQuery()
                ->where($orderColumn, '>', $orderValue)
                ->decrement($orderColumn);
        });

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function (Model $model): void {
                $orderColumn = $model->determineOrderColumnName();
                $orderValue = $model->$orderColumn;

                $model->buildSortQuery()
                    ->where($orderColumn, '>=', $orderValue)
                    ->whereKeyNot($model->getKey())
                    ->increment($orderColumn);
            });
        }
    }

    public function getIsSorted(): bool
    {
        return $this->isSorted;
    }

    public function moveToPosition(int $newPosition): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $maxOrder = $this->getHighestOrderNumber();
        $currentOrder = $this->$orderColumnName ?? $maxOrder + 1;
        $newPosition = max(1, min($newPosition, $maxOrder)); // Ensure the new position is valid

        if ($currentOrder === $newPosition) {
            return $this; // No need to move if already in the desired position
        }

        // If moving up
        if ($newPosition < $currentOrder) {
            static::buildSortQuery()
                ->whereBetween($orderColumnName, [$newPosition, $currentOrder - 1])
                ->increment($orderColumnName);
        }

        // If moving down
        if ($newPosition > $currentOrder) {
            static::buildSortQuery()
                ->whereBetween($orderColumnName, [$currentOrder + 1, $newPosition])
                ->decrement($orderColumnName);
        }

        // Set the model's order column to the new position
        $this->$orderColumnName = $newPosition;
        $this->setIsSorted()->save();

        return $this;
    }

    protected function setIsSorted(bool $isSorted = true): static
    {
        $this->isSorted = $isSorted;

        return $this;
    }
}
