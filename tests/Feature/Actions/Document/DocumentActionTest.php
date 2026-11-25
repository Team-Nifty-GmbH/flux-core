<?php

use FluxErp\Actions\Document\DeleteDocument;
use FluxErp\Actions\Document\DownloadDocument;
use FluxErp\Actions\Document\DownloadDocuments;
use FluxErp\Actions\Document\ReviewDocument;
use FluxErp\Actions\Document\UpdateDocument;
use FluxErp\Actions\Document\UploadDocument;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Support\MediaLibrary\FluxMediaStream;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

beforeEach(function (): void {
    config(['filesystems.disks.private' => ['driver' => 'local', 'root' => storage_path('app/private')]]);
    Storage::fake('private');
    Queue::fake();

    $this->employeeUser = User::factory()->create(['is_active' => true]);
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->employeeUser->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $this->documentType = DocumentType::factory()->create([
        'name' => 'Contract',
        'model_types' => ['employee'],
        'retention_years' => 6,
        'is_owner_uploadable' => true,
        'is_active' => true,
    ]);
});

function documentPdf(string $name = 'contract.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent(
        $name,
        "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n"
    );
}

function uploadDocument(Employee $employee, DocumentType $documentType, array $data = []): Media
{
    return UploadDocument::make(array_merge([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'document_type_id' => $documentType->getKey(),
        'document_date' => '2025-03-10',
        'media' => documentPdf(),
    ], $data))
        ->validate()
        ->execute();
}

function actAsEmployeeWithoutHrRights(User $user): void
{
    Permission::findOrCreate('action.document.review', 'web');
    Permission::findOrCreate('action.document.download', 'web');
    Permission::findOrCreate('action.document.upload', 'web');

    test()->actingAs($user, 'web');
}

test('hr upload is approved and locked until the end of the retention period', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);

    expect($media->document_type_id)->toBe($this->documentType->getKey())
        ->and($media->collection_name)->toBe('documents')
        ->and($media->review_state)->toBe(DocumentReviewStateEnum::Approved)
        ->and($media->reviewed_by)->toBe($this->user->getMorphClass() . ':' . $this->user->getKey())
        ->and($media->document_date->toDateString())->toBe('2025-03-10')
        ->and($media->retain_until->toDateString())->toBe('2031-12-31');
});

test('document type without retention years has no lock', function (): void {
    $this->documentType->update(['retention_years' => null]);

    $media = uploadDocument($this->employee, $this->documentType);

    expect($media->review_state)->toBe(DocumentReviewStateEnum::Approved)
        ->and($media->retain_until)->toBeNull()
        ->and(DeleteDocument::make(['id' => $media->getKey()])->validate()->execute())->toBeTrue()
        ->and(Media::query()->withoutGlobalScopes()->whereKey($media->getKey())->exists())->toBeFalse();
});

test('owner upload is pending without retention', function (): void {
    actAsEmployeeWithoutHrRights($this->employeeUser);

    $media = uploadDocument($this->employee, $this->documentType);

    expect($media->review_state)->toBe(DocumentReviewStateEnum::Pending)
        ->and($media->retain_until)->toBeNull()
        ->and($media->reviewed_by)->toBeNull();
});

test('owner upload refuses a document type the owner may not use', function (array $attributes): void {
    actAsEmployeeWithoutHrRights($this->employeeUser);
    $this->documentType->update($attributes);

    expect(fn () => uploadDocument($this->employee, $this->documentType))
        ->toThrow(ValidationException::class);

    expect(Media::query()->withoutGlobalScopes()->count())->toBe(0);
})
    ->with([
        'not owner uploadable' => [['is_owner_uploadable' => false]],
        'inactive' => [['is_active' => false]],
        'other model type' => [['model_types' => ['contact']]],
    ]);

test('hr upload refuses an inactive document type or one for another model', function (array $attributes): void {
    $this->documentType->update($attributes);

    expect(fn () => uploadDocument($this->employee, $this->documentType))
        ->toThrow(ValidationException::class);
})
    ->with([
        'inactive' => [['is_active' => false]],
        'other model type' => [['model_types' => ['contact']]],
    ]);

test('a user who neither owns the record nor has the permission cannot upload', function (): void {
    actAsEmployeeWithoutHrRights(User::factory()->create(['is_active' => true]));

    expect(fn () => uploadDocument($this->employee, $this->documentType))
        ->toThrow(UnauthorizedException::class);
});

test('approving a pending document sets the retention lock', function (): void {
    actAsEmployeeWithoutHrRights($this->employeeUser);
    $media = uploadDocument($this->employee, $this->documentType);
    $this->actingAs($this->user, 'web');
    $this->user->givePermissionTo('action.document.review');

    $reviewed = ReviewDocument::make([
        'id' => $media->getKey(),
        'review_state' => DocumentReviewStateEnum::Approved->value,
    ])
        ->validate()
        ->execute();

    expect($reviewed->review_state)->toBe(DocumentReviewStateEnum::Approved)
        ->and($reviewed->reviewed_by)->toBe($this->user->getMorphClass() . ':' . $this->user->getKey())
        ->and($reviewed->retain_until->toDateString())->toBe('2031-12-31');
});

test('only pending documents can be reviewed', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);

    expect(fn () => ReviewDocument::make([
        'id' => $media->getKey(),
        'review_state' => DocumentReviewStateEnum::Rejected->value,
    ])->validate()->execute())
        ->toThrow(ValidationException::class);

    expect($media->refresh()->review_state)->toBe(DocumentReviewStateEnum::Approved);
});

test('a rejected document keeps its file and can be deleted', function (): void {
    actAsEmployeeWithoutHrRights($this->employeeUser);
    $media = uploadDocument($this->employee, $this->documentType);
    $this->actingAs($this->user, 'web');
    $this->user->givePermissionTo('action.document.review');

    $rejected = ReviewDocument::make([
        'id' => $media->getKey(),
        'review_state' => DocumentReviewStateEnum::Rejected->value,
        'review_note' => 'Wrong person',
    ])
        ->validate()
        ->execute();

    expect($rejected->review_state)->toBe(DocumentReviewStateEnum::Rejected)
        ->and($rejected->review_note)->toBe('Wrong person')
        ->and($rejected->retain_until)->toBeNull()
        ->and(Storage::disk('private')->exists($rejected->getPathRelativeToRoot()))->toBeTrue()
        ->and(DeleteDocument::make(['id' => $media->getKey()])->validate()->execute())->toBeTrue();
});

test('a locked document cannot be deleted before its retention ends', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);

    expect(fn () => DeleteDocument::make(['id' => $media->getKey()])->validate()->execute())
        ->toThrow(ValidationException::class);

    expect(Media::query()->withoutGlobalScopes()->whereKey($media->getKey())->exists())->toBeTrue()
        ->and(Storage::disk('private')->exists($media->getPathRelativeToRoot()))->toBeTrue();

    $this->travelTo('2032-01-01');

    expect(DeleteDocument::make(['id' => $media->getKey()])->validate()->execute())->toBeTrue()
        ->and(Media::query()->withoutGlobalScopes()->whereKey($media->getKey())->exists())->toBeFalse();
});

test('retention can only be extended', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);

    $updated = UpdateDocument::make(['id' => $media->getKey(), 'retain_until' => '2035-12-31'])
        ->validate()
        ->execute();

    expect($updated->retain_until->toDateString())->toBe('2035-12-31');

    expect(fn () => UpdateDocument::make(['id' => $media->getKey(), 'retain_until' => '2033-12-31'])
        ->validate()
        ->execute())
        ->toThrow(ValidationException::class);

    expect(fn () => UpdateDocument::make(['id' => $media->getKey(), 'retain_until' => null])
        ->validate()
        ->execute())
        ->toThrow(ValidationException::class);

    expect($media->refresh()->retain_until->toDateString())->toBe('2035-12-31');
});

test('the owner may download their own document', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);
    actAsEmployeeWithoutHrRights($this->employeeUser);

    $response = DownloadDocument::make(['id' => $media->getKey()])->validate()->execute();

    expect($response->getStatusCode())->toBe(200);
});

test('another employee cannot download a foreign document', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);
    $otherUser = User::factory()->create(['is_active' => true]);
    app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $otherUser->getKey(),
        'firstname' => 'Other',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
    actAsEmployeeWithoutHrRights($otherUser);

    expect(fn () => DownloadDocument::make(['id' => $media->getKey()])->validate()->execute())
        ->toThrow(UnauthorizedException::class);
});

test('documents are exported as zip by record, year and type', function (): void {
    uploadDocument($this->employee, $this->documentType);

    $stream = DownloadDocuments::make([
        'model_type' => morph_alias(Employee::class),
        'model_ids' => [$this->employee->getKey()],
    ])
        ->validate()
        ->execute();

    expect($stream)->toBeInstanceOf(FluxMediaStream::class);

    $path = tempnam(sys_get_temp_dir(), 'documents');
    ob_start();
    $stream->getZipStream();
    file_put_contents($path, ob_get_clean());

    $zip = new ZipArchive();
    $zip->open($path);
    $entries = collect(range(0, $zip->numFiles - 1))->map(fn (int $index) => $zip->getNameIndex($index));
    $zip->close();
    unlink($path);

    expect($entries->all())->toBe(['Test Employee/2025/Contract/contract.pdf']);
});

test('the zip export leaves out documents that are not approved', function (): void {
    actAsEmployeeWithoutHrRights($this->employeeUser);
    uploadDocument($this->employee, $this->documentType);
    $this->actingAs($this->user, 'web');

    $stream = DownloadDocuments::make([
        'model_type' => morph_alias(Employee::class),
        'model_ids' => [$this->employee->getKey()],
    ])
        ->validate()
        ->execute();

    ob_start();
    $stream->getZipStream();
    $content = ob_get_clean();

    expect($content)->not->toContain('contract.pdf');
});

test('force deleting a record with a locked document is refused', function (): void {
    $media = uploadDocument($this->employee, $this->documentType);

    expect(fn () => $this->employee->forceDelete())->toThrow(ValidationException::class);

    expect(Employee::query()->withTrashed()->whereKey($this->employee->getKey())->exists())->toBeTrue()
        ->and(Media::query()->withoutGlobalScopes()->whereKey($media->getKey())->exists())->toBeTrue();
});

test('force deleting a record removes its unlocked documents', function (): void {
    $this->documentType->update(['retention_years' => null]);
    $media = uploadDocument($this->employee, $this->documentType);

    $this->employee->forceDelete();

    expect(Media::query()->withoutGlobalScopes()->whereKey($media->getKey())->exists())->toBeFalse();
});
