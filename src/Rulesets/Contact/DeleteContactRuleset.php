<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Contact;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteContactRuleset extends FluxRuleset
{
    protected static ?string $model = Contact::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Contact::class),
            ],
        ];
    }
}
