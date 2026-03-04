<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Rulesets\Contact\CreateContactRuleset;
use Illuminate\Support\Arr;

class CreateContact extends FluxAction
{
    public static function models(): array
    {
        return [Contact::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateContactRuleset::class;
    }

    public function performAction(): Contact
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups');
        $discounts = Arr::pull($this->data, 'discounts');
        $mainAddress = Arr::pull($this->data, 'main_address');
        $industries = Arr::pull($this->data, 'industries');
        $tenants = Arr::pull($this->data, 'tenants');

        $contact = app(Contact::class, ['attributes' => $this->data]);
        $contact->save();

        if ($tenants) {
            $contact->tenants()->attach($tenants);
        }

        if ($discounts) {
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

        if ($industries) {
            $contact->industries()->attach($industries);
        }

        if (! $this->getData('customer_number')) {
            $contact->getSerialNumber('customer_number');
        }

        if (is_array($mainAddress)) {
            $mainAddress['contact_id'] = $contact->id;

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
        $this->data['price_list_id'] ??= resolve_static(PriceList::class, 'default')?->getKey();
        $this->data['payment_type_id'] ??= resolve_static(PaymentType::class, 'default')?->getKey();
        $this->data['currency_id'] ??= resolve_static(Currency::class, 'default')?->getKey();
    }
}
