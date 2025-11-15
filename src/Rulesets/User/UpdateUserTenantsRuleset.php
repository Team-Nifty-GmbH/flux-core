<?php

namespace FluxErp\Rulesets\User;

use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateUserTenantsRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = User::class;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'tenants' => 'present|array',
            'tenants.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
        ];
    }
}
