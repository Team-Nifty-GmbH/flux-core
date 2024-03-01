<?php

namespace FluxErp\Rulesets\PushSubscription;

use FluxErp\Rulesets\FluxRuleset;

class UpsertPushSubscriptionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'endpoint' => [
                'required',
                'url',
            ],
            'keys' => [
                'required',
                'array',
            ],
            'keys.auth' => [
                'required',
                'string',
            ],
            'keys.p256dh' => [
                'required',
                'string',
            ],
        ];
    }
}
