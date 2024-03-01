<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Rulesets\Address\DeleteAddressRuleset;

class DeleteAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteAddressRuleset::class, 'getRules');
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
