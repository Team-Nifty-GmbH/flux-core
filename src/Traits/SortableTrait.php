<?php

namespace FluxErp\Traits;

use Spatie\EloquentSortable\SortableTrait as BaseSortableTrait;

trait SortableTrait
{
    use BaseSortableTrait;

    public function moveToPosition(int $newPosition): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $maxOrder = $this->getHighestOrderNumber();
        $currentOrder = $this->$orderColumnName;
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
        $this->save();

        return $this;
    }
}
