<?php

namespace FluxErp\Rulesets\Client;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\OrderType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateClientRuleset extends FluxRuleset
{
    protected static ?string $model = Client::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:clients,uuid',
            'country_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'commission_credit_note_order_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class])
                    ->where('order_type_enum', OrderTypeEnum::Refund)
                    ->where('is_active', true),
            ],
            'name' => 'required|string',
            'client_code' => 'required|string|unique:clients,client_code',
            'ceo' => 'string|nullable',
            'street' => 'string|nullable',
            'city' => 'string|nullable',
            'postcode' => 'string|nullable',
            'phone' => 'string|nullable',
            'fax' => 'string|nullable',
            'email' => 'email|nullable',
            'website' => 'string|nullable',
            'creditor_identifier' => 'string|nullable',
            'vat_id' => 'string|nullable',
            'sepa_text' => 'string|nullable',
            'opening_hours' => 'array|nullable',
            'terms_and_conditions' => 'string|nullable',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules')
        );
    }
}
