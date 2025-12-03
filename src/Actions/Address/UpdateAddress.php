<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\ContactOption\DeleteContactOption;
use FluxErp\Actions\ContactOption\UpdateContactOption;
use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Address\UpdateAddressRuleset;
use FluxErp\Settings\CoreSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UpdateAddress extends FluxAction
{
    public static function models(): array
    {
        return [Address::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateAddressRuleset::class;
    }

    public function performAction(): Model
    {
        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $tags = Arr::pull($this->data, 'tags');
        $contactOptions = Arr::pull($this->data, 'contact_options');
        $this->data['has_formal_salutation'] ??= app(CoreSettings::class)->formal_salutation;

        if (! data_get($this->data, 'is_main_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_main_address', true)
                ->exists()
        ) {
            $this->data['is_main_address'] = true;
        }

        if (! data_get($this->data, 'is_invoice_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_invoice_address', true)
                ->exists()
        ) {
            $this->data['is_invoice_address'] = true;
        }

        if (! data_get($this->data, 'is_delivery_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_delivery_address', true)
                ->exists()
        ) {
            $this->data['is_delivery_address'] = true;
        }

        $address->fill($this->data);
        $address->save();

        if (! is_null($tags)) {
            $address->syncTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        if (! is_null($contactOptions)) {
            Helper::updateRelatedRecords(
                model: $address,
                related: $contactOptions,
                relation: 'contactOptions',
                foreignKey: 'address_id',
                createAction: CreateContactOption::class,
                updateAction: UpdateContactOption::class,
                deleteAction: DeleteContactOption::class
            );
        }

        if ($this->data['address_types'] ?? false) {
            $addressTypes = resolve_static(AddressType::class, 'query')
                ->whereIntegerInRaw('id', $this->data['address_types'])
                ->where('is_unique', true)
                ->whereHas('addresses', fn (Builder $query) => $query->where('contact_id', $this->data['contact_id'])
                    ->where('id', '!=', $this->data['id'])
                )
                ->get();

            foreach ($addressTypes as $addressType) {
                $addressType->addresses()->detach($address->contact->addresses()->get());
            }

            $address->addressTypes()->sync($this->data['address_types']);
        }

        return $address->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (array_key_exists('email_primary', $this->data)) {
            $this->data['email_primary'] = is_string($this->getData('email_primary'))
                ? Str::between($this->getData('email_primary'), '<', '>')
                : null;
        }
    }
}
