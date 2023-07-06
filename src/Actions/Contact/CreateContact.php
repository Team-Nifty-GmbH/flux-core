<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateContactRequest;
use FluxErp\Models\Contact;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateContact implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateContactRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'contact.create';
    }

    public static function description(): string|null
    {
        return 'create contact';
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function execute(): Contact
    {
        $this->data['customer_number'] = $this->data['customer_number'] ?? uniqid();
        $discountGroups = Arr::pull($this->data, 'discount_groups', []);

        $contact = new Contact($this->data);
        $contact->save();

        if ($discountGroups) {
            $contact->discountGroups()->attach($discountGroups);
        }

        return $contact;
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

        return $this;
    }
}
