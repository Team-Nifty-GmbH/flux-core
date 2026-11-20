<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;

trait BindsModelRecord
{
    public function updatingModelId(mixed $value): void
    {
        if (is_null($value) || $this->canBindRecord($value)) {
            return;
        }

        throw new CannotUpdateLockedPropertyException('modelId');
    }

    protected function canBindRecord(mixed $id): bool
    {
        if (! auth()->user() instanceof User) {
            return false;
        }

        $detailRoute = method_exists($this->modelType, 'getDetailRouteName')
            ? app($this->modelType)->getDetailRouteName()
            : null;

        return (blank($detailRoute) || user_can_access_route($detailRoute))
            && resolve_static($this->modelType, 'query')
                ->whereKey($id)
                ->exists();
    }
}
