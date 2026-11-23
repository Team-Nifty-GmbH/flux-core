<?php

use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Exceptions\FileIsBlocked;
use FluxErp\Models\BlockedFile;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $this->path = tempnam(sys_get_temp_dir(), 'logo_');
    file_put_contents($this->path, 'logo content');
    $this->upload = fn (): Media => UploadMedia::make([
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
        'media' => $this->path,
        'file_name' => 'logo.txt',
    ])->validate()->execute();
});

test('a file that is not blocked is stored', function (): void {
    expect(($this->upload)())->toBeInstanceOf(Media::class);
});

test('a blocked file is rejected on upload', function (): void {
    BlockedFile::query()->create(['hash' => md5_file($this->path), 'file_name' => 'logo.txt']);

    expect(fn () => ($this->upload)())->toThrow(ValidationException::class)
        ->and(Media::query()->where('model_id', $this->contact->getKey())->count())->toBe(0);
});

test('a blocked file cannot be copied to another model', function (): void {
    $media = ($this->upload)();
    BlockedFile::query()->create(['hash' => md5_file($this->path), 'file_name' => 'logo.txt']);

    expect(fn () => $media->copy(Contact::factory()->create()))->toThrow(FileIsBlocked::class);
});
