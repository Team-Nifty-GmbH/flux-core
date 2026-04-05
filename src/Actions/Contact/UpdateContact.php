<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\ContactTenant;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Contact\UpdateContactRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateContact extends FluxAction
{
    public static function models(): array
    {
        return [Contact::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateContactRuleset::class;
    }

    public function performAction(): Model
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups');
        $discounts = Arr::pull($this->data, 'discounts');
        $industries = Arr::pull($this->data, 'industries');
        $tenants = Arr::pull($this->data, 'tenants');

        $contact = resolve_static(Contact::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $contact->fill($this->data);
        $contact->save();

        if (! is_null($tenants)) {
            $contact->tenants()->sync($tenants);
        }

        if (! is_null($discounts)) {
            $syncType = match ($this->getData('discounts_pivot_sync_type')) {
                'attach' => 'attach',
                'detach' => 'detach',
                'syncWithoutDetaching' => 'syncWithoutDetaching',
                default => 'sync',
            };

            $selectedDiscounts = [];

            foreach ($discounts as $discount) {
                if ($discountId = data_get($discount, 'id')) {
                    $selectedDiscounts[] = $discountId;

                    continue;
                }

                if ($syncType !== 'detach') {
                    $selectedDiscounts[] = CreateDiscount::make($discount)
                        ->checkPermission()
                        ->validate()
                        ->execute()
                        ->getKey();
                }
            }

            $contact->discounts()->{$syncType}($selectedDiscounts);
        }

        if (! is_null($discountGroups)) {
            $contact->discountGroups()->sync($discountGroups);
        }

        if (! is_null($industries)) {
            $contact->industries()->sync($industries);
        }

        return $contact->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $contact = resolve_static(Contact::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $this->data['payment_type_id'] ??= $contact?->payment_type_id;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];

        if ($customerNumber = $this->getData('customer_number')) {
            $currentCustomerNumber = resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('customer_number');

            if ($customerNumber !== $currentCustomerNumber) {
                $customerNumberExists = resolve_static(Contact::class, 'query')
                    ->whereKeyNot($this->getData('id'))
                    ->where('customer_number', $customerNumber)
                    ->exists();

                if ($customerNumberExists) {
                    $errors += [
                        'customer_number' => ['Customer number already exists'],
                    ];
                }
            }
        }

        $paymentTypeId = array_key_exists('payment_type_id', $this->data)
            ? $this->getData('payment_type_id')
            : resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('payment_type_id');
        if ($paymentTypeId && ($paymentTypeId || $this->getData('tenants'))) {
            $tenants = $this->getData('tenants') ?? resolve_static(ContactTenant::class, 'query')
                ->where('contact_id', $this->getData('id'))
                ->pluck('tenant_id')
                ->toArray();

            if (resolve_static(PaymentType::class, 'query')
                ->whereKey($paymentTypeId)
                ->whereHasTenant(
                    $tenants ?: resolve_static(Tenant::class, 'query')->pluck('id')->toArray()
                )
                ->doesntExist()
            ) {
                $errors += [
                    'payment_type_id' => [
                        __(
                            'Payment type with id: \':paymentTypeId\' doesnt match with the associated tenants',
                            [
                                'paymentTypeId' => $paymentTypeId,
                            ]
                        ),
                    ],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateContact');
        }
    }
}
