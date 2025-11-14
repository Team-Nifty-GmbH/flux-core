<?php

namespace FluxErp\Actions\DeviceToken;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DeviceToken;
use FluxErp\Rulesets\DeviceToken\CreateDeviceTokenRuleset;

class CreateDeviceToken extends FluxAction
{
    public static function models(): array
    {
        return [DeviceToken::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateDeviceTokenRuleset::class;
    }

    public function performAction(): DeviceToken
    {
        $deviceToken = app(DeviceToken::class, ['attributes' => $this->getData()]);
        $deviceToken->save();

        return $deviceToken->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_active'] ??= true;
    }
}
