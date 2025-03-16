<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteUserRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = User::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
        ];
    }
}
