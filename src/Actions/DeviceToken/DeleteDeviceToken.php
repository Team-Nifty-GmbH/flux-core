<?php

namespace FluxErp\Actions\DeviceToken;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DeviceToken;
use FluxErp\Rulesets\DeviceToken\DeleteDeviceTokenRuleset;

class DeleteDeviceToken extends FluxAction
{
    public static function models(): array
    {
        return [DeviceToken::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteDeviceTokenRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(DeviceToken::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
