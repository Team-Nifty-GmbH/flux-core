<?php

namespace FluxErp\Actions\CustomEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\CustomEvent;

class DeleteCustomEvent extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:custom_events,id',
        ];
    }

    public static function models(): array
    {
        return [CustomEvent::class];
    }

    public function execute(): ?bool
    {
        return CustomEvent::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
