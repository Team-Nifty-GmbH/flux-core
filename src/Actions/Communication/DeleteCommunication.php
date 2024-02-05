<?php

namespace FluxErp\Actions\Communication;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Communication;

class DeleteCommunication extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:communications,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Communication::class];
    }

    public function performAction(): ?bool
    {
        return Communication::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
