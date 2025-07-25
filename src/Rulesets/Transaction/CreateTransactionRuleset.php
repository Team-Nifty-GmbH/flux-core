<?php

namespace FluxErp\Rulesets\Transaction;

use FluxErp\Models\BankConnection;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Transaction;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateTransactionRuleset extends FluxRuleset
{
    protected static ?string $model = Transaction::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(CategoryRuleset::class, 'getRules'),
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:transactions,uuid',
            'bank_connection_id' => [
                'required_without:contact_bank_connection_id',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => BankConnection::class]),
            ],
            'contact_bank_connection_id' => [
                'required_without:bank_connection_id',
                'exclude_unless:bank_connection_id,null',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => ContactBankConnection::class])
                    ->where('is_credit_account', true),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'value_date' => 'required|date',
            'booking_date' => 'required|date',
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'purpose' => 'string|nullable',
            'type' => 'string|max:255|nullable',
            'counterpart_name' => 'string|max:255|nullable',
            'counterpart_account_number' => 'string|max:255|nullable',
            'counterpart_iban' => [
                'string',
                'nullable',
                'max:255',
                app(Iban::class),
            ],
            'counterpart_bic' => 'string|max:255|nullable',
            'counterpart_bank_name' => 'string|max:255|nullable',
            'is_ignored' => 'boolean',
        ];
    }
}
