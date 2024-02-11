<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CustomEvent;

/**
 * @deprecated
 */
class DeleteCustomEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:custom_events,id',
        ];
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function performAction(): ?bool
    {
        return CustomEvent::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
