<?php

namespace FluxErp\Actions\DeviceToken;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DeviceToken;
use FluxErp\Rulesets\DeviceToken\UpdateDeviceTokenRuleset;

class UpdateDeviceToken extends FluxAction
{
    public static function models(): array
    {
        return [DeviceToken::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateDeviceTokenRuleset::class;
    }

    public function performAction(): DeviceToken
    {
        $deviceToken = resolve_static(DeviceToken::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $deviceToken->fill($this->getData());
        $deviceToken->save();

        return $deviceToken->refresh();
    }
}
