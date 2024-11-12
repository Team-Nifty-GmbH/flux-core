<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait as BaseSortableTrait;

trait SortableTrait
{
    private bool $isSorted = false;

    use BaseSortableTrait {
        BaseSortableTrait::bootSortableTrait as bootParentSortableTrait;
    }

    protected static function bootSortableTrait(): void
    {
        static::bootParentSortableTrait();

        static::saving(function (Model $model) {
            $orderColumn = $model->determineOrderColumnName();

            if ($model->isDirty($orderColumn) && ! $model->getIsSorted() && $model->exists) {
                $newPosition = $model->$orderColumn ?? $model->getHighestOrderNumber() + 1;
                $model->$orderColumn = $model->getRawOriginal($orderColumn);

                $model->moveToPosition($newPosition);
            }
        });
    }

    protected function setIsSorted(bool $isSorted = true): static
    {
        $this->isSorted = $isSorted;

        return $this;
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
}
