<?php

namespace FluxErp\Listeners\Order;

use FluxErp\Events\Order\DocumentSignedEvent;
use FluxErp\Models\Order;
use FluxErp\States\Order\Draft;
use FluxErp\States\Order\InReview;
use FluxErp\States\Order\Open;

class OfferSignedSubscriber
{
    public function handle(DocumentSignedEvent $event): void
    {
        $order = $event->signature->model;

        if (! $order instanceof Order || $event->signature->name !== 'signature-offer') {
            return;
        }

        if ($order->state instanceof Draft && $order->state->canTransitionTo(Open::class)) {
            $order->state->transitionTo(Open::class);
        }

        if ($order->state->canTransitionTo(InReview::class)) {
            $order->state->transitionTo(InReview::class);
        }
    }

    public function subscribe(): array
    {
        return [
            resolve_static(DocumentSignedEvent::class, 'class') => 'handle',
        ];
    }
}
