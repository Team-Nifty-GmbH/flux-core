<?php

use FluxErp\Models\User;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

beforeEach(function (): void {
    // Touching the storage helper triggers Livewire's Storage::fake() for the test disk.
    FileUploadConfiguration::storage();
});

function chunkInit(array $headers = ['Upload-Length' => '1024', 'Upload-Name' => null]): Illuminate\Testing\TestResponse
{
    $headers['Upload-Name'] ??= base64_encode('test.pdf');

    return test()->call(
        method: 'POST',
        uri: route('file-pond.chunk'),
        server: test()->transformHeadersToServerVars($headers)
    );
}

function transferIdFrom(Illuminate\Testing\TestResponse $response): string
{
    return data_get($response->json(), 'data.transfer_id');
}

test('chunk init requires authentication', function (): void {
    auth('web')->logout();

    $response = $this->postJson(route('file-pond.chunk'), [], [
        'Upload-Length' => 1024,
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $response->assertUnauthorized();
});

test('chunk init returns a signed transfer id', function (): void {
    $response = chunkInit();

    $response->assertOk();

    $signedPath = transferIdFrom($response);

    expect($signedPath)
        ->toMatch('/^[a-f0-9]{8}:[A-Za-z0-9]{40}\.pdf$/');

    $filename = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);

    expect($filename)->not->toBeFalse();

    $disk = FileUploadConfiguration::storage();
    expect($disk->exists(FileUploadConfiguration::path($filename)))->toBeTrue();
    expect($disk->exists(FileUploadConfiguration::path($filename . '.chunk')))->toBeTrue();
});

test('chunk init rejects empty upload length', function (): void {
    $response = chunkInit([
        'Upload-Length' => '0',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $response->assertStatus(422);
});

test('chunk init rejects invalid extension', function (): void {
    $response = chunkInit([
        'Upload-Length' => '1024',
        'Upload-Name' => base64_encode('no-extension'),
    ]);

    $response->assertStatus(422);
});

test('chunk init rejects files exceeding max size', function (): void {
    config(['flux.file_uploads.max_size' => '1K']);

    $response = chunkInit([
        'Upload-Length' => '4096',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $response->assertStatus(413);
});

test('multiple chunks assemble into the final file', function (): void {
    config(['livewire.temporary_file_upload.rules' => ['file']]);

    $body = random_bytes(2048);
    $totalSize = strlen($body);

    $initResponse = chunkInit([
        'Upload-Length' => (string) $totalSize,
        'Upload-Name' => base64_encode('payload.bin'),
    ]);

    $initResponse->assertOk();
    $signedPath = transferIdFrom($initResponse);

    $patchUrl = route('file-pond.chunk') . '?patch=' . urlencode($signedPath);

    $chunkSize = 512;
    $offset = 0;
    while ($offset < $totalSize) {
        $end = min($offset + $chunkSize, $totalSize);
        $chunk = substr($body, $offset, $end - $offset);

        $response = $this->call(
            method: 'PATCH',
            uri: $patchUrl,
            server: $this->transformHeadersToServerVars([
                'Upload-Offset' => (string) $offset,
                'Upload-Length' => (string) $totalSize,
                'Content-Type' => 'application/offset+octet-stream',
            ]),
            content: $chunk,
        );

        $response->assertOk();
        expect(data_get($response->json(), 'data.offset'))->toBe($end);

        $offset = $end;
    }

    $filename = TemporaryUploadedFile::extractPathFromSignedPath($signedPath);
    $disk = FileUploadConfiguration::storage();

    expect($disk->exists(FileUploadConfiguration::path($filename)))->toBeTrue();
    expect($disk->exists(FileUploadConfiguration::path($filename . '.chunk')))->toBeFalse();
    expect($disk->exists(FileUploadConfiguration::path($filename . '.json')))->toBeTrue();

    $stored = $disk->get(FileUploadConfiguration::path($filename));
    expect($stored)->toBe($body);

    $meta = json_decode($disk->get(FileUploadConfiguration::path($filename . '.json')), true);
    expect($meta['name'])->toBe('payload.bin');
    expect($meta['size'])->toBe($totalSize);
});

test('chunk patch rejects mismatching offsets', function (): void {
    $initResponse = chunkInit([
        'Upload-Length' => '2048',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $signedPath = transferIdFrom($initResponse);

    $response = $this->call(
        method: 'PATCH',
        uri: route('file-pond.chunk') . '?patch=' . urlencode($signedPath),
        server: $this->transformHeadersToServerVars([
            'Upload-Offset' => '500',
            'Content-Type' => 'application/offset+octet-stream',
        ]),
        content: str_repeat('a', 100),
    );

    $response->assertStatus(409);
});

test('chunk patch rejects requests from other users', function (): void {
    $initResponse = chunkInit([
        'Upload-Length' => '2048',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $signedPath = transferIdFrom($initResponse);

    $otherUser = User::factory()->create([
        'is_active' => true,
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
    $this->be($otherUser, 'web');

    $response = $this->call(
        method: 'PATCH',
        uri: route('file-pond.chunk') . '?patch=' . urlencode($signedPath),
        server: $this->transformHeadersToServerVars([
            'Upload-Offset' => '0',
            'Content-Type' => 'application/offset+octet-stream',
        ]),
        content: str_repeat('a', 100),
    );

    $response->assertForbidden();
});

test('chunk patch rejects invalid signed path', function (): void {
    $response = $this->call(
        method: 'PATCH',
        uri: route('file-pond.chunk') . '?patch=' . urlencode('00000000:not-a-real-file.pdf'),
        server: $this->transformHeadersToServerVars([
            'Upload-Offset' => '0',
            'Content-Type' => 'application/offset+octet-stream',
        ]),
        content: 'data',
    );

    $response->assertStatus(422);
});

test('chunk patch rejects chunks that exceed the declared upload length', function (): void {
    $initResponse = chunkInit([
        'Upload-Length' => '128',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $initResponse->assertOk();
    $signedPath = transferIdFrom($initResponse);

    $response = $this->call(
        method: 'PATCH',
        uri: route('file-pond.chunk') . '?patch=' . urlencode($signedPath),
        server: $this->transformHeadersToServerVars([
            'Upload-Offset' => '0',
            'Content-Type' => 'application/offset+octet-stream',
        ]),
        content: str_repeat('a', 256),
    );

    $response->assertStatus(413);
});

test('chunk patch rejects empty bodies', function (): void {
    $initResponse = chunkInit([
        'Upload-Length' => '128',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $initResponse->assertOk();
    $signedPath = transferIdFrom($initResponse);

    $response = $this->call(
        method: 'PATCH',
        uri: route('file-pond.chunk') . '?patch=' . urlencode($signedPath),
        server: $this->transformHeadersToServerVars([
            'Upload-Offset' => '0',
            'Content-Type' => 'application/offset+octet-stream',
        ]),
        content: '',
    );

    $response->assertStatus(422);
});

test('chunk init rejects negative upload length', function (): void {
    $response = chunkInit([
        'Upload-Length' => '-1',
        'Upload-Name' => base64_encode('test.pdf'),
    ]);

    $response->assertStatus(422);
});
