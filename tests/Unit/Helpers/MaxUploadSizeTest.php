<?php

use FluxErp\Helpers\Helper;
use Illuminate\Support\Number;

test('the livewire rule caps the upload size', function (): void {
    config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:1024']]);

    expect(Helper::getMaxUploadSizeInBytes())->toBe(1024 * 1024);
});

test('a rule given as a string is understood too', function (): void {
    config(['livewire.temporary_file_upload.rules' => 'required|file|max:2048']);

    expect(Helper::getMaxUploadSizeInBytes())->toBe(2048 * 1024);
});

test('without a rule the livewire default applies', function (): void {
    config(['livewire.temporary_file_upload.rules' => null]);

    expect(Helper::getMaxUploadSizeInBytes())->toBe(
        min(
            12288 * 1024,
            (int) Number::fromFileSizeToBytes(ini_get('upload_max_filesize')),
            (int) Number::fromFileSizeToBytes(ini_get('post_max_size')),
        )
    );
});

test('the php limits win when they are lower', function (): void {
    config(['livewire.temporary_file_upload.rules' => ['file', 'max:1048576']]);

    expect(Helper::getMaxUploadSizeInBytes())
        ->toBeLessThanOrEqual((int) Number::fromFileSizeToBytes(ini_get('upload_max_filesize')));
});

test('ini values without a unit suffix are understood', function (): void {
    expect(invade_helper('8388608'))->toBe(8388608)
        ->and(invade_helper('8M'))->toBe(8 * 1024 * 1024)
        ->and(invade_helper('1G'))->toBe(1024 ** 3)
        ->and(invade_helper('512K'))->toBe(512 * 1024);
});

test('unlimited or unparseable ini values do not count as a limit', function (): void {
    expect(invade_helper('0'))->toBe(0)
        ->and(invade_helper('-1'))->toBe(0)
        ->and(invade_helper(''))->toBe(0)
        ->and(invade_helper('kaputt'))->toBe(0);
});

test('the fallback applies when no limit can be determined', function (): void {
    config(['livewire.temporary_file_upload.rules' => ['file']]);

    expect(Helper::getMaxUploadSizeInBytes())->toBeGreaterThan(0);
});

function invade_helper(string $value): int
{
    return (fn (string $size): int => static::parseIniSizeToBytes($size))
        ->call(new Helper(), $value);
}
