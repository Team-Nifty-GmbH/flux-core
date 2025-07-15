<?php

namespace FluxErp\Rulesets\SepaMandate;

use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

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
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'contact_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'contact_bank_connection_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => ContactBankConnection::class]),
            ],
            'type' => [
                'required',
                Rule::enum(SepaMandateTypeEnum::class),
            ],
            'signed_date' => 'sometimes|date|nullable',
        ];
    }
}
