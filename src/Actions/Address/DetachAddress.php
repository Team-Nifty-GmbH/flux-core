<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Rulesets\Address\DetachAddressRuleset;
use Illuminate\Validation\ValidationException;

class DetachAddress extends FluxAction
{
    public static function models(): array
    {
        return [Address::class];
    }

    protected function getRulesets(): string|array
    {
        return DetachAddressRuleset::class;
    }

    public function performAction(): Address
    {
        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->getData('id'))
            ->with('contact.tenants:id')
            ->first();

        $origin = $address->contact;

        // The address keeps trading under the same terms it had before, so the
        // new contact inherits the commercial master data and the tenants.
        $contact = CreateContact::make([
            'agent_id' => $origin->agent_id,
            'currency_id' => $origin->currency_id,
            'language_id' => $origin->language_id,
            'payment_type_id' => $origin->payment_type_id,
            'price_list_id' => $origin->price_list_id,
            'tenants' => $origin->tenants->pluck('id')->toArray(),
        ])
            ->validate()
            ->execute();

        return MoveAddress::make([
            'id' => $address->getKey(),
            'contact_id' => $contact->getKey(),
        ])
            ->validate()
            ->execute();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'contact_id']);

        if (resolve_static(Address::class, 'query')
            ->whereKeyNot($address->getKey())
            ->where('contact_id', $address->contact_id)
            ->doesntExist()
        ) {
            throw ValidationException::withMessages([
                'id' => ['The contact would be left without an address.'],
            ])
                ->errorBag('detachAddress');
        }
    }
}
