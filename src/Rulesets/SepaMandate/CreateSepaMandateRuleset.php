<?php

namespace FluxErp\Rulesets\SepaMandate;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateSepaMandateRuleset extends FluxRuleset
{
    protected static ?string $model = SepaMandate::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:sepa_mandates,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'required',
                'integer',
                new ModelExists(Contact::class),
            ],
            'contact_bank_connection_id' => [
                'required',
                'integer',
                new ModelExists(ContactBankConnection::class),
            ],
            'signed_date' => 'sometimes|date|nullable',
        ];
    }
}
