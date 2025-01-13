<?php

namespace FluxErp\Events\Order;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentSignedEvent
{
    public function __construct(public Media $signature) {}

    public function broadcastChannel(): string
    {
        return $this->signature->model->broadcastChannel();
    }
}
