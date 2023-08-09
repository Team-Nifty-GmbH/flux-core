<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateContactRequest;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateContact extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateContactRequest())->rules();
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function performAction(): Model
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups');

        $contact = Contact::query()
            ->whereKey($this->data['id'])
            ->first();

        $contact->fill($this->data);
        $contact->save();

        if (! is_null($discountGroups)) {
            $contact->discountGroups()->sync($discountGroups);
        }

        return $contact->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Contact());

        $this->data = $validator->validate();

        $errors = [];
        $contact = Contact::query()
            ->whereKey($this->data['id'])
            ->first();

        $this->data['payment_type_id'] = $this->data['payment_type_id'] ?? $contact->payment_type_id;
        $this->data['client_id'] = $this->data['client_id'] ?? $contact->client_id;

        if (array_key_exists('customer_number', $this->data)) {
            $customerNumberExists = Contact::query()
                ->where('id', '!=', $this->data['id'])
                ->where('client_id', '=', $this->data['client_id'])
                ->where('customer_number', $this->data['customer_number'])
                ->exists();

            if ($customerNumberExists) {
                $errors += [
                    'customer_number' => [__('Customer number already exists')],
                ];
            }
        }

        if (array_key_exists('creditor_number', $this->data) && ! is_null($this->data['creditor_number'])) {
            $customerNumberExists = Contact::query()
                ->where('id', '!=', $this->data['id'])
                ->where('client_id', '=', $this->data['client_id'])
                ->where('creditor_number', $this->data['creditor_number'])
                ->exists();

            if ($customerNumberExists) {
                $errors += [
                    'creditor_number' => [__('Creditor number already exists')],
                ];
            }
        }

        if (array_key_exists('debtor_number', $this->data) && ! is_null($this->data['debtor_number'])) {
            $customerNumberExists = Contact::query()
                ->where('id', '!=', $this->data['id'])
                ->where('client_id', '=', $this->data['client_id'])
                ->where('debtor_number', $this->data['debtor_number'])
                ->exists();

            if ($customerNumberExists) {
                $errors += [
                    'debtor_number' => [__('Debtor number already exists')],
                ];
            }
        }

        $clientPaymentTypeExists = PaymentType::query()
            ->whereKey($this->data['payment_type_id'])
            ->where('client_id', $this->data['client_id'])
            ->exists();

        if (! $clientPaymentTypeExists) {
            $errors += [
                'payment_type_id' => [
                    __(
                        'Payment type with id: \':paymentTypeId\' doesnt match client id: \':clientId\'',
                        [
                            'paymentTypeId' => $this->data['payment_type_id'],
                            'clientId' => $this->data['client_id'],
                        ]
                    ),
                ],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateContact');
        }
    }
}
