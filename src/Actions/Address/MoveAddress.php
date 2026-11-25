<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Rulesets\Address\MoveAddressRuleset;
use Illuminate\Validation\ValidationException;

class MoveAddress extends FluxAction
{
    public static function models(): array
    {
        return [Address::class];
    }

    protected function getRulesets(): string|array
    {
        return MoveAddressRuleset::class;
    }

    public function performAction(): Address
    {
        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $address->contact_id = $this->getData('contact_id');
        $address->save();

        // Leaving a contact strips the main address flag, and it does so with a
        // query update the instance knows nothing about. Refreshing first is
        // what makes the flag dirty again, so a target that had no addresses
        // does not end up without a main address.
        $address->refresh();

        if (! resolve_static(Address::class, 'query')
            ->where('contact_id', $this->getData('contact_id'))
            ->where('is_main_address', true)
            ->exists()
        ) {
            $address->is_main_address = true;
            $address->save();
        }

        return $address->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $currentContactId = resolve_static(Address::class, 'query')
            ->whereKey($this->getData('id'))
            ->value('contact_id');

        if ($currentContactId === $this->getData('contact_id')) {
            throw ValidationException::withMessages([
                'contact_id' => ['The address already belongs to this contact.'],
            ])
                ->errorBag('moveAddress');
        }
    }
}
