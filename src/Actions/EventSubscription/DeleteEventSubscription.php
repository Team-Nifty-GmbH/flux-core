<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\DeleteEventSubscriptionRuleset;

class DeleteEventSubscription extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteEventSubscriptionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function performAction(): ?bool
    {
        return app(EventSubscription::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
