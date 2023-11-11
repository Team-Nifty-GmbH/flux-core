<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateContactRequest;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateContact extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateContactRequest())->rules();
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function performAction(): Contact
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups', []);

        $contact = new Contact($this->data);
        $contact->save();

        if ($discountGroups) {
            $contact->discountGroups()->attach($discountGroups);
        }

        if (! ($this->data['customer_number'] ?? false)) {
            $contact->getSerialNumber(
                'customer_number',
                $contact->client_id,
            );
        }

        return $contact->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Contact());

        $this->data = $validator->validate();
    }
}
