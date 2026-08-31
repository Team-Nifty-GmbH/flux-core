<?php

namespace FluxErp\Support\MediaLibrary;

use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedFile
{
    public const CHUNK_BYTES = 1024 * 1024;

    /**
     * Filesystem::response() streams through fpassthru(), which maps the file and hands
     * it to the output layer in one write, so an active output buffer allocates the whole
     * file size at once. Copying in bounded chunks keeps memory flat regardless of size.
     */
    public static function response(FilesystemAdapter $disk, string $path, array $headers = []): StreamedResponse
    {
        $headers['Content-Type'] ??= $disk->mimeType($path);
        $headers['Content-Length'] ??= $disk->size($path);

        return response()->stream(
            function () use ($disk, $path): void {
                $stream = $disk->readStream($path);

                while (! feof($stream)) {
                    echo fread($stream, static::CHUNK_BYTES);
                }

                fclose($stream);
            },
            headers: $headers
        );
    }
}
