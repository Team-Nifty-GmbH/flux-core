<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FilePondChunkController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        if (FileUploadConfiguration::isUsingS3() || FileUploadConfiguration::isUsingGCS()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 501,
                statusMessage: 'Chunked uploads require a local disk',
            );
        }

        return match ($request->method()) {
            'POST' => $this->init($request),
            'PATCH' => $this->patch($request),
            default => ResponseHelper::methodNotAllowed('Method Not Allowed'),
        };
    }

    protected function init(Request $request): JsonResponse
    {
        $length = (int) $request->header('Upload-Length');
        $name = base64_decode((string) $request->header('Upload-Name'), true) ?: $request->header('Upload-Name');

        if ($length <= 0) {
            return ResponseHelper::unprocessableEntity('Invalid Upload-Length header');
        }

        if (! is_string($name) || trim($name) === '') {
            return ResponseHelper::unprocessableEntity('Invalid Upload-Name header');
        }

        if ($length > $this->maxUploadSize()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 413,
                statusMessage: 'File exceeds the maximum upload size',
            );
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension === '' || ! preg_match('/^[a-z0-9]+$/', $extension)) {
            return ResponseHelper::unprocessableEntity('Invalid file extension');
        }

        $filename = Str::random(40) . '.' . $extension;
        $signedPath = TemporaryUploadedFile::signPath($filename);

        $disk = FileUploadConfiguration::storage();
        $user = auth()->user();

        $disk->put(FileUploadConfiguration::path($filename), '');
        $disk->put(FileUploadConfiguration::path($filename . '.chunk'), json_encode([
            'expected_size' => $length,
            'original_name' => $name,
            'started_at' => now()->timestamp,
            'user_id' => $user?->getKey(),
            'user_type' => $user?->getMorphClass(),
        ]));

        return ResponseHelper::ok(
            statusMessage: 'ok',
            data: ['transfer_id' => $signedPath],
        );
    }

    protected function patch(Request $request): JsonResponse
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
            return ResponseHelper::conflict(
                'Upload-Offset does not match',
                ['offset' => $currentSize],
            );
        }

        $body = $request->getContent();
        $chunkSize = strlen($body);

        if ($chunkSize === 0) {
            return ResponseHelper::unprocessableEntity('Empty chunk');
        }

        if ($offset + $chunkSize > $expected) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 413,
                statusMessage: 'Chunk exceeds declared upload length',
            );
        }

        $handle = fopen($absolutePath, 'ab');
        if ($handle === false) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 500,
                statusMessage: 'Unable to open upload target',
            );
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
            return ResponseHelper::createResponseFromBase(
                statusCode: 500,
                statusMessage: 'Failed to write full chunk',
            );
        }

        $newSize = $offset + $chunkSize;

        if ($newSize === $expected) {
            $finalizeError = $this->finalize($disk, $filename, $session);
            if ($finalizeError) {
                return $finalizeError;
            }
        }

        return ResponseHelper::ok(
            statusMessage: 'ok',
            data: ['offset' => $newSize],
        );
    }

    /**
     * @return array{0: ?FilesystemAdapter, 1: ?string, 2: ?array, 3: ?JsonResponse}
     */
    protected function resolveTransfer(Request $request): array
    {
        $signedPath = (string) $request->query('patch');

        if (! preg_match('/^[a-f0-9]{8}:[A-Za-z0-9]{40}\.[a-z0-9]+$/', $signedPath)) {
            return [null, null, null, ResponseHelper::unprocessableEntity('Invalid transfer id')];
        }

        $filename = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
        if ($filename === false) {
            return [null, null, null, ResponseHelper::createResponseFromBase(
                statusCode: 403,
                statusMessage: 'Invalid signature',
            )];
        }

        $disk = FileUploadConfiguration::storage();
        $sessionPath = FileUploadConfiguration::path($filename . '.chunk');

        if (! $disk->exists($sessionPath)) {
            return [null, null, null, ResponseHelper::notFound('Unknown transfer')];
        }

        $session = json_decode($disk->get($sessionPath), true) ?: [];
        if (! ($session['expected_size'] ?? null)) {
            return [null, null, null, ResponseHelper::createResponseFromBase(
                statusCode: 500,
                statusMessage: 'Corrupt transfer session',
            )];
        }

        $user = auth()->user();
        $sessionUserId = $session['user_id'] ?? null;
        $sessionUserType = $session['user_type'] ?? null;

        if ($sessionUserId !== $user?->getKey() || $sessionUserType !== $user?->getMorphClass()) {
            return [null, null, null, ResponseHelper::createResponseFromBase(
                statusCode: 403,
                statusMessage: 'Forbidden',
            )];
        }

        return [$disk, $filename, $session, null];
    }

    protected function finalize(FilesystemAdapter $disk, string $filename, array $session): ?JsonResponse
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

            return ResponseHelper::unprocessableEntity($validator->errors()->first('file'));
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
