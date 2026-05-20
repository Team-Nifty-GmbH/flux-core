<?php

namespace FluxErp\Support\MediaLibrary;

use Illuminate\Support\Facades\URL;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class UrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        // Conversions and originals can live on different disks (e.g. originals on s3,
        // conversions on the local public disk). Pick the disk that actually backs the
        // file we're being asked for, not just the original's disk.
        $effectiveDisk = is_null($this->conversion)
            ? $this->media->disk
            : $this->media->conversions_disk;

        if ($effectiveDisk !== 'public' && $this->media->getKey() && $this->media->file_name) {
            $params = [
                'media' => $this->media->getKey(),
                'filename' => $this->media->file_name,
            ];

            if (! is_null($this->conversion)) {
                $params['conversion'] = $this->conversion->getName();
            }

            return URL::temporarySignedRoute('media.private', now()->addMinutes(5), $params);
        }

        return parent::getUrl();
    }
}
