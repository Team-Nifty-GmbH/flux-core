<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateContactRequest;
use FluxErp\Models\Contact;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateContact extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateContactRequest())->rules();
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

        return $contact->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Contact());

        $this->data = $validator->validate();

        return $this;
    }
}
