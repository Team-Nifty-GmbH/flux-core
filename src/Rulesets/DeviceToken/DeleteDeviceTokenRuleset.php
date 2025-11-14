<?php

namespace FluxErp\Rulesets\DeviceToken;

use FluxErp\Models\DeviceToken;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDeviceTokenRuleset extends FluxRuleset
{
    protected static ?string $model = DeviceToken::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DeviceToken::class]),
            ],
        ];
    }
}
