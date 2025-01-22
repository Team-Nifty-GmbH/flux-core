<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\Contact\CreateContactRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateContact extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateContactRuleset::class;
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function performAction(): Contact
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups');
        $discounts = Arr::pull($this->data, 'discounts');
        $mainAddress = Arr::pull($this->data, 'main_address');

        $contact = app(Contact::class, ['attributes' => $this->data]);
        $contact->save();

        if (! is_null($discounts)) {
            $attachDiscounts = [];

            foreach ($discounts as $discount) {
                if ($discountId = data_get($discount, 'id')) {
                    $attachDiscounts[] = $discountId;

                    continue;
                }

                $attachDiscounts[] = CreateDiscount::make($discount)
                    ->checkPermission()
                    ->validate()
                    ->execute()
                    ->getKey();
            }

            $contact->discounts()->attach($attachDiscounts);
        }

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

    protected function prepareForValidation(): void
    {
        $this->data['client_id'] ??= Client::default()?->id;
        $this->data['price_list_id'] ??= PriceList::default()?->id;
        $this->data['payment_type_id'] ??= PaymentType::default()?->id;
        $this->data['currency_id'] ??= Currency::default()?->id;
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Contact::class));

        $this->data = $validator->validate();
    }
}
