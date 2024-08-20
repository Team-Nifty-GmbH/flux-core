<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\Client;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateUserClientsRuleset extends FluxRuleset
{
    protected static ?string $model = User::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'clients' => 'present|array',
            'clients.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
        ];
    }
}
