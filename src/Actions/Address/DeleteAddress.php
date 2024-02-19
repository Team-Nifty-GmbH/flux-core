<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Rules\ModelExists;

class DeleteAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
        ];
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): ?bool
    {
        $address = app(Address::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $address->addressTypes()->detach();
        $address->tokens()->delete();

        return $address->delete();
    }
}
