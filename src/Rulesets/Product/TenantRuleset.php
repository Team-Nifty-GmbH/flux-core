<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Tenant;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class TenantRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'tenants' => 'array',
            'tenants.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
        ];
    }
}
