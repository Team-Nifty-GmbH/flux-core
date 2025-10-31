<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Contact;
use FluxErp\Models\Country;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Address\CreateAddressRuleset;
use FluxErp\Settings\CoreSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateAddress extends FluxAction
{
    public static function models(): array
    {
        return [Address::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAddressRuleset::class;
    }

    public function performAction(): Address
    {
        $tags = Arr::pull($this->data, 'tags');
        $permissions = Arr::pull($this->data, 'permissions');

        if (! data_get($this->data, 'is_main_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'])
                ->where('is_main_address', true)
                ->exists()
        ) {
            $this->data['is_main_address'] = true;
        }

        if (! data_get($this->data, 'is_invoice_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'])
                ->where('is_invoice_address', true)
                ->exists()
        ) {
            $this->data['is_invoice_address'] = true;
        }

        if (! data_get($this->data, 'is_delivery_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'])
                ->where('is_delivery_address', true)
                ->exists()
        ) {
            $this->data['is_delivery_address'] = true;
        }

        $contactOptions = Arr::pull($this->data, 'contact_options', []);

        /** @var Address $address */
        $address = app(Address::class, ['attributes' => $this->data]);
        $address->save();

        if ($tags) {
            $address->attachTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        if ($permissions) {
            $address->givePermissionTo($permissions);
        }

        if (resolve_static(CreateContactOption::class, 'canPerformAction', [false])) {
            foreach ($contactOptions as $contactOption) {
                $contactOption['address_id'] = $address->id;
                CreateContactOption::make($contactOption)
                    ->validate()
                    ->execute();
            }
        }

        if ($this->data['address_types'] ?? false) {
            $addressTypes = resolve_static(AddressType::class, 'query')
                ->whereIntegerInRaw('id', $this->data['address_types'])
                ->where('is_unique', true)
                ->whereHas(
                    'addresses',
                    fn (Builder $query) => $query->where('contact_id', $this->data['contact_id'])
                )
                ->get();

            foreach ($addressTypes as $addressType) {
                $addressType->addresses()->detach($address->contact->addresses->pluck('id')->toArray());
            }

            foreach ($this->data['address_types'] as $addressType) {
                $address->addressTypes()->attach($addressType);
            }
        }

        return $address->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['country_id'] ??= resolve_static(Country::class, 'default')?->getKey();
        $this->data['email_primary'] = is_string($this->getData('email_primary'))
            ? Str::between($this->getData('email_primary'), '<', '>')
            : null;
        $this->data['email'] = is_string($this->getData('email'))
            ? Str::between($this->getData('email'), '<', '>')
            : null;
        $this->data['has_formal_salutation'] ??= app(CoreSettings::class)->formal_salutation;
        $this->data['client_id'] ??= resolve_static(Contact::class, 'query')
            ->whereKey($this->getData('contact_id'))
            ->value('client_id');
    }
}
