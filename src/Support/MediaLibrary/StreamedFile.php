<?php

namespace FluxErp\Support\MediaLibrary;

use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedFile
{
    public const CHUNK_BYTES = 1024 * 1024;

    /**
     * A drop-in for FilesystemAdapter::response(), including how the file name is
     * determined: a Content-Disposition passed in wins, otherwise the header is built
     * from $name, and $name falls back to the basename of the path.
     *
     * It exists because FilesystemAdapter::response() streams through fpassthru(), which
     * maps the file and hands it to the output layer in one write, so an active output
     * buffer allocates the whole file size at once. Copying in bounded chunks keeps
     * memory flat regardless of size.
     */
    public static function response(
        FilesystemAdapter $disk,
        string $path,
        ?string $name = null,
        array $headers = []
    ): StreamedResponse {
        $headers['Content-Type'] ??= $disk->mimeType($path);
        $headers['Content-Length'] ??= $disk->size($path);
        $headers['Content-Disposition'] ??= ContentDisposition::make(
            HeaderUtils::DISPOSITION_INLINE,
            $name ?? basename($path)
        );

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
