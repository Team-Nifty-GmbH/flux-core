<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateContactRequest;
use FluxErp\Models\Contact;
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

        $contact = new Contact($this->data);
        $contact->save();

        return $contact;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
