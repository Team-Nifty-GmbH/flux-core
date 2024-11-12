<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\DeleteEventSubscriptionRuleset;

class DeleteEventSubscription extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteEventSubscriptionRuleset::class;
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(EventSubscription::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
