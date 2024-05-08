<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\Contact\CreateContactRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateContact extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateContactRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function performAction(): Contact
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups');
        $mainAddress = Arr::pull($this->data, 'main_address');

        $this->data['price_list_id'] = $this->data['price_list_id'] ?? PriceList::default()?->id;
        $this->data['payment_type_id'] = $this->data['payment_type_id'] ?? PaymentType::default()?->id;

        $contact = app(Contact::class, ['attributes' => $this->data]);
        $contact->save();

        if (is_array($discountGroups)) {
            $contact->discountGroups()->attach($discountGroups);
        }

        if (! ($this->data['customer_number'] ?? false)) {
            $contact->getSerialNumber(
                'customer_number',
                $contact->client_id,
            );
        }

        if (is_array($mainAddress)) {
            $mainAddress['contact_id'] = $contact->id;
            $mainAddress['client_id'] = $contact->client_id;

            $mainAddress = CreateAddress::make($mainAddress)
                ->validate()
                ->execute();

            $contact->main_address_id = $mainAddress->id;
            $contact->save();
        }

        return $contact->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Contact::class));

        $this->data = $validator->validate();
    }
}
