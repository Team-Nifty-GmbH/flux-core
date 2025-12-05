<?php

namespace FluxErp\Rulesets\PaymentType;

use FluxErp\Models\Tenant;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class TenantRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'tenants' => 'sometimes|required|array',
            'tenants.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
        ];
    }
}
