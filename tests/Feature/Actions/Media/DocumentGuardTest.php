<?php

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\DownloadMultipleMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Storage::fake('public');
    Queue::fake();

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $this->document = Media::factory()->create([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $this->employee->getKey(),
        'collection_name' => 'documents',
        'name' => 'secret-contract',
        'file_name' => 'secret-contract.pdf',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'document_type_id' => DocumentType::factory()->create()->getKey(),
        'document_date' => '2025-03-10',
        'review_state' => DocumentReviewStateEnum::Approved,
        'review_note' => 'confidential note',
    ]);

    Storage::disk('public')->put($this->document->getPathRelativeToRoot(), 'content');
});

function documentStillExists(Media $document): bool
{
    return Media::query()->withoutGlobalScopes()->whereKey($document->getKey())->exists();
}

test('generic media actions refuse a document', function (string $action, Closure $data): void {
    expect(fn () => $action::make($data($this->document))->validate()->execute())
        ->toThrow(ValidationException::class);

    expect(documentStillExists($this->document))->toBeTrue()
        ->and($this->document->refresh()->file_name)->toBe('secret-contract.pdf');
})
    ->with([
        'download' => [DownloadMedia::class, fn (Media $media) => ['id' => $media->getKey()]],
        'download multiple' => [DownloadMultipleMedia::class, fn (Media $media) => ['media' => [$media->getKey()]]],
        'update' => [UpdateMedia::class, fn (Media $media) => ['id' => $media->getKey(), 'file_name' => 'renamed']],
        'replace' => [
            ReplaceMedia::class,
            fn (Media $media) => ['id' => $media->getKey(), 'media' => UploadedFile::fake()->create('other.pdf')],
        ],
        'delete' => [DeleteMedia::class, fn (Media $media) => ['id' => $media->getKey()]],
    ]);

test('deleting a media collection leaves its documents', function (): void {
    DeleteMediaCollection::make([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $this->employee->getKey(),
        'collection_name' => 'documents',
    ])
        ->validate()
        ->execute();

    expect(documentStillExists($this->document))->toBeTrue();
});

test('uploading plain media cannot create a document', function (): void {
    $media = UploadMedia::make([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $this->employee->getKey(),
        'collection_name' => 'attachments',
        'disk' => 'public',
        'media' => UploadedFile::fake()->create('note.txt', 1, 'text/plain'),
        'document_type_id' => $this->document->document_type_id,
        'review_state' => DocumentReviewStateEnum::Approved->value,
        'retain_until' => '2099-12-31',
    ])
        ->validate()
        ->execute();

    expect(Media::query()->withoutGlobalScopes()->whereKey($media->getKey())->first())
        ->document_type_id->toBeNull()
        ->review_state->toBeNull()
        ->retain_until->toBeNull();
});

test('documents are not part of the media of a record', function (): void {
    expect($this->employee->getMedia('documents'))->toBeEmpty()
        ->and($this->employee->media()->count())->toBe(0);
});

test('documents cannot be probed through relation filters in the search', function (array $payload): void {
    $response = $this->post(
        route('search', Employee::class),
        array_merge(['search' => 'Test', 'searchFields' => ['name']], $payload)
    )
        ->assertOk();

    expect($response->json('*.id') ?? [])->not->toContain($this->employee->getKey());
})
    ->with([
        'where has' => [['whereHas' => 'media']],
        'where relation' => [['whereRelation' => ['media', 'review_note', 'confidential note']]],
    ]);
