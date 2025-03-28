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
        $this->setIsSorted();

        $orderColumnName = $this->determineOrderColumnName();
        $maxOrder = $this->getHighestOrderNumber();

        // Check if this is a new record
        $exists = static::buildSortQuery()
            ->whereKey($this->getKey())
            ->exists();

        // Get current position (for existing records) or max+1 (for new records)
        $currentOrder = $exists ? $this->$orderColumnName : $maxOrder + 1;

        // Ensure the new position is valid (between 1 and max+1)
        $newPosition = max(1, min($newPosition, $maxOrder + 1));

        if (! $exists) {
            // For new records: make space at the target position
            static::buildSortQuery()
                ->where($orderColumnName, '>=', $newPosition)
                ->increment($orderColumnName);
        } else {
            // For existing records: handle reordering
            if ($newPosition < $currentOrder) {
                // Moving up: shift items in between down
                static::buildSortQuery()
                    ->whereKeyNot($this->getKey())
                    ->whereBetween($orderColumnName, [$newPosition, $currentOrder - 1])
                    ->increment($orderColumnName);
            } elseif ($newPosition > $currentOrder) {
                // Moving down: shift items in between up
                static::buildSortQuery()
                    ->whereKeyNot($this->getKey())
                    ->whereBetween($orderColumnName, [$currentOrder + 1, $newPosition])
                    ->decrement($orderColumnName);
            }
        }

        // Set the model's order column to the new position
        $this->$orderColumnName = $newPosition;
        $this->save();

        return $this;
    }

    protected function setIsSorted(bool $isSorted = true): static
    {
        $this->isSorted = $isSorted;

        return $this;
    }
}
