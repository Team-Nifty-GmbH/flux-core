<?php

namespace FluxErp\Events\Order;

use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentSignedEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Media $signature) {}
}
