<?php

namespace FluxErp\Helpers;

use Spatie\MediaLibrary\Downloaders\Downloader;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class MediaLibraryDownloader implements Downloader
{
    /**
     * @throws UnreachableUrl
     */
    public function getTempFile(string $url): string
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: MyAppName/1.0\r\n",
            ],
        ];
        $context = stream_context_create($options);
        if (! $stream = @fopen($url, 'r', false, $context)) {
            throw UnreachableUrl::create($url);
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($temporaryFile, $stream);

        return $temporaryFile;
    }
}
