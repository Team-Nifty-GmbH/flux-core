<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateContactRequest;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateContact implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateContactRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'contact.update';
    }

    public static function description(): string|null
    {
        return 'update contact';
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function execute(): Model
    {
        $contact = Contact::query()
            ->whereKey($this->data['id'])
            ->first();

        $contact->fill($this->data);
        $contact->save();

        return $contact->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
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
                    'customer_number' => [__('Customer number already exists')]
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
                    )
                ]
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateContact');
        }

        return $this;
    }
}
