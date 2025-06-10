<?php

namespace FluxErp\Rulesets\MailFolder;

use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateMailFolderRuleset extends FluxRuleset
{
    protected static ?string $model = MailFolder::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MailFolder::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => MailFolder::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255',
            'can_create_ticket' => 'boolean',
            'can_create_purchase_invoice' => 'boolean',
            'can_create_lead' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
