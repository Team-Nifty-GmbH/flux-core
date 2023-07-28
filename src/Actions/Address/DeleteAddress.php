<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Address;

class DeleteAddress extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): ?bool
    {
        $address = Address::query()
            ->whereKey($this->data['id'])
            ->first();

        $address->addressTypes()->detach();
        $address->tokens()->delete();

        return $address->delete();
    }
}
