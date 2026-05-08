<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FilePondChunkController extends Controller
{
    private const TRANSFER_ID_PATTERN = '/^[a-f0-9]{8}:[A-Za-z0-9]{40}\.[a-z0-9]+$/';

    public function handle(Request $request): Response
    {
        if (! $this->diskSupportsChunking()) {
            return response('Chunked uploads require a local disk', 501);
        }

        return match ($request->method()) {
            'POST' => $this->init($request),
            'PATCH' => $this->patch($request),
            'HEAD' => $this->head($request),
            default => response('Method Not Allowed', 405),
        };
    }

    protected function diskSupportsChunking(): bool
    {
        if (FileUploadConfiguration::isUsingS3() || FileUploadConfiguration::isUsingGCS()) {
            return false;
        }

        $config = FileUploadConfiguration::diskConfig();

        return is_array($config) && ($config['driver'] ?? null) === 'local';
    }

    protected function init(Request $request): Response
    {
        $length = (int) $request->header('Upload-Length');
        $name = base64_decode((string) $request->header('Upload-Name'), true) ?: $request->header('Upload-Name');

        if ($length <= 0) {
            return response('Invalid Upload-Length header', 400);
        }

        if (! is_string($name) || trim($name) === '') {
            return response('Invalid Upload-Name header', 400);
        }

        if ($length > $this->maxUploadSize()) {
            return response('File exceeds the maximum upload size', 413);
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension === '' || ! preg_match('/^[a-z0-9]+$/', $extension)) {
            return response('Invalid file extension', 422);
        }

        $filename = Str::random(40) . '.' . $extension;
        $signedPath = TemporaryUploadedFile::signPath($filename);

        $disk = FileUploadConfiguration::storage();

        $user = auth('web')->user();

        $disk->put(FileUploadConfiguration::path($filename), '');
        $disk->put(FileUploadConfiguration::path($filename . '.chunk'), json_encode([
            'expected_size' => $length,
            'original_name' => $name,
            'started_at' => now()->timestamp,
            'user_id' => $user?->getKey(),
            'user_type' => $user?->getMorphClass(),
        ]));

        return response($signedPath, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    protected function patch(Request $request): Response
    {
        [$disk, $filename, $session, $error] = $this->resolveTransfer($request);
        if ($error) {
            return $error;
        }

        $offset = (int) $request->header('Upload-Offset');
        $expected = (int) $session['expected_size'];

        $absolutePath = $disk->path(FileUploadConfiguration::path($filename));
        $currentSize = file_exists($absolutePath) ? filesize($absolutePath) : 0;

        if ($offset !== $currentSize) {
            return response('Upload-Offset does not match', 409, [
                'Upload-Offset' => (string) $currentSize,
            ]);
        }

        $body = $request->getContent();
        $chunkSize = strlen($body);

        if ($chunkSize === 0) {
            return response('Empty chunk', 400);
        }

        if ($offset + $chunkSize > $expected) {
            return response('Chunk exceeds declared upload length', 413);
        }

        $handle = fopen($absolutePath, 'ab');
        if ($handle === false) {
            return response('Unable to open upload target', 500);
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new FileException('Unable to lock upload target');
            }
            $written = fwrite($handle, $body);
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        if ($written !== $chunkSize) {
            return response('Failed to write full chunk', 500);
        }

        $newSize = $offset + $chunkSize;

        if ($newSize === $expected) {
            $finalizeError = $this->finalize($disk, $filename, $session);
            if ($finalizeError) {
                return $finalizeError;
            }
        }

        return response('', 204, [
            'Upload-Offset' => (string) $newSize,
        ]);
    }

    protected function head(Request $request): Response
    {
        [$disk, $filename, , $error] = $this->resolveTransfer($request);
        if ($error) {
            return $error;
        }

        $absolutePath = $disk->path(FileUploadConfiguration::path($filename));
        $currentSize = file_exists($absolutePath) ? filesize($absolutePath) : 0;

        return response('', 200, [
            'Upload-Offset' => (string) $currentSize,
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * @return array{0: ?FilesystemAdapter, 1: ?string, 2: ?array, 3: ?Response}
     */
    protected function resolveTransfer(Request $request): array
    {
        $signedPath = (string) $request->query('patch');

        if (! preg_match(self::TRANSFER_ID_PATTERN, $signedPath)) {
            return [null, null, null, response('Invalid transfer id', 400)];
        }

        $filename = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
        if ($filename === false) {
            return [null, null, null, response('Invalid signature', 403)];
        }

        $disk = FileUploadConfiguration::storage();
        $sessionPath = FileUploadConfiguration::path($filename . '.chunk');

        if (! $disk->exists($sessionPath)) {
            return [null, null, null, response('Unknown transfer', 404)];
        }

        $session = json_decode($disk->get($sessionPath), true) ?: [];
        if (! isset($session['expected_size'])) {
            return [null, null, null, response('Corrupt transfer session', 500)];
        }

        $user = auth('web')->user();
        $sessionUserId = $session['user_id'] ?? null;
        $sessionUserType = $session['user_type'] ?? null;

        if ($sessionUserId !== $user?->getKey() || $sessionUserType !== $user?->getMorphClass()) {
            return [null, null, null, response('Forbidden', 403)];
        }

        return [$disk, $filename, $session, null];
    }

    protected function finalize(FilesystemAdapter $disk, string $filename, array $session): ?Response
    {
        $absolutePath = $disk->path(FileUploadConfiguration::path($filename));

        $disk->put(FileUploadConfiguration::path($filename . '.json'), json_encode([
            'name' => $session['original_name'],
            'type' => mime_content_type($absolutePath) ?: 'application/octet-stream',
            'size' => filesize($absolutePath),
            'hash' => $filename,
        ]));

        $disk->delete(FileUploadConfiguration::path($filename . '.chunk'));

        $rules = config('flux.file_uploads.chunk_rules')
            ?? config('livewire.temporary_file_upload.rules')
            ?? ['file'];

        $uploadedFile = TemporaryUploadedFile::createFromLivewire($filename);

        $validator = Validator::make(['file' => $uploadedFile], ['file' => $rules]);

        if ($validator->fails()) {
            $disk->delete(FileUploadConfiguration::path($filename));
            $disk->delete(FileUploadConfiguration::path($filename . '.json'));

            return response($validator->errors()->first('file'), 422);
        }

        return null;
    }

    protected function maxUploadSize(): int
    {
        $configured = config('flux.file_uploads.max_size');

        if ($configured) {
            return (int) Number::fromFileSizeToBytes($configured);
        }

        return 5 * 1024 * 1024 * 1024;
    }
}
