<?php

namespace FluxErp\Events\Order;

use FluxErp\Support\Event\SubscribableEvent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentSignedEvent extends SubscribableEvent
{
    public function __construct(public Media $signature) {}
}
