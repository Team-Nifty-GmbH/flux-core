<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\CustomEvent;

class DeleteCustomEvent extends BaseAction
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
