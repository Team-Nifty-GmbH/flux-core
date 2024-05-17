<?php

namespace FluxErp\Support\MediaLibrary;

use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class UrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if ($this->media->disk !== 'public' && $this->media->id && $this->media->file_name) {
            return URL::temporarySignedRoute('media.private', now()->addMinutes(5), [
                'media' => $this->media->id,
                'filename' => $this->media->file_name,
            ]);
        }

        return parent::getUrl();
    }
}
