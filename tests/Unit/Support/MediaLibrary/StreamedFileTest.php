<?php

use FluxErp\Support\MediaLibrary\StreamedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->disk = Storage::fake('streamed-file');
    $this->disk->putFileAs('', UploadedFile::fake()->image('Bild.png'), 'stored-name.png');
});

test('the file name falls back to the basename of the path', function (): void {
    expect(StreamedFile::response($this->disk, 'stored-name.png')->headers->get('Content-Disposition'))
        ->toContain('filename=stored-name.png');
});

test('the file name can be set by the caller', function (): void {
    $disposition = StreamedFile::response($this->disk, 'stored-name.png', 'Polen-Militär.png')
        ->headers
        ->get('Content-Disposition');

    expect($disposition)
        ->toContain("filename*=utf-8''Polen-Milit%C3%A4r.png")
        ->toContain('filename=Polen-Militar.png');
});

test('a content disposition passed in wins over the file name', function (): void {
    $response = StreamedFile::response(
        $this->disk,
        'stored-name.png',
        'Ignored.png',
        ['Content-Disposition' => 'attachment; filename=Chosen.png']
    );

    expect($response->headers->get('Content-Disposition'))->toBe('attachment; filename=Chosen.png');
});
