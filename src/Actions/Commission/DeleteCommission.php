<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Commission;

class DeleteCommission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:commissions,id',
        ];
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): ?bool
    {
        return Commission::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
