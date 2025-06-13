<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Contact;
use FluxErp\Rules\ModelDoesntExist;
use FluxErp\Rules\ModelExists;

class RestoreContactRuleset extends CreateContactRuleset
{
    protected static ?string $model = Contact::class;

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'id' => [
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => Contact::class])
                        ->onlyTrashed(),
                ],
                'customer_number' => [
                    'string',
                    'max:255',
                    'nullable',
                    app(ModelDoesntExist::class, ['model' => Contact::class, 'key' => 'customer_number']),
                ],
                'creditor_number' => [
                    'string',
                    'max:255',
                    'nullable',
                    app(ModelDoesntExist::class, ['model' => Contact::class, 'key' => 'creditor_number']),
                ],
                'debtor_number' => [
                    'string',
                    'max:255',
                    'nullable',
                    app(ModelDoesntExist::class, ['model' => Contact::class, 'key' => 'debtor_number']),
                ],
            ]
        );
    }
}
