<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CustomEvent;

/**
 * @deprecated
 */
class DeleteCustomEvent extends FluxAction
{
    public static function models(): array
    {
        return [CustomEvent::class];
    }

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:custom_events,id',
        ];
    }

    public function performAction(): ?bool
    {
        return CustomEvent::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
