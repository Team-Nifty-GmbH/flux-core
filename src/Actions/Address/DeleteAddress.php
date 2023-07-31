<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Address;

class DeleteAddress extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function execute(): ?bool
    {
        $address = Address::query()
            ->whereKey($this->data['id'])
            ->first();

        $address->addressTypes()->detach();
        $address->tokens()->delete();

        return $address->delete();
    }
}
