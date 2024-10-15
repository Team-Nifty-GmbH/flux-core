<?php

namespace FluxErp\Support\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\MediaCollection as BaseMediaCollection;

class MediaCollection extends BaseMediaCollection
{
    public bool $readOnly = false;

    public function readOnly(): static
    {
        $this->readOnly = true;

        return $this;
    }
}
