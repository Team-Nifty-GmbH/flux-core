<?php

use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Livewire\Employee\Documents;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    config(['filesystems.disks.private' => ['driver' => 'local', 'root' => storage_path('app/private')]]);
    Storage::fake('private');
    Queue::fake();

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $this->documentType = DocumentType::factory()->create([
        'name' => 'Contract',
        'model_types' => ['employee'],
        'retention_years' => 6,
        'is_active' => true,
    ]);
});

function personnelFileDocument(Employee $employee, DocumentType $documentType): Media
{
    return Media::factory()->create([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'collection_name' => 'documents',
        'disk' => 'private',
        'document_type_id' => $documentType->getKey(),
        'document_date' => '2025-03-10',
        'review_state' => DocumentReviewStateEnum::Approved,
    ]);
}

test('renders successfully', function (): void {
    Livewire::test(Documents::class, ['employeeId' => $this->employee->getKey()])
        ->assertOk();
});

test('lists only the documents of the employee', function (): void {
    $own = personnelFileDocument($this->employee, $this->documentType);
    $other = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Other',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
    personnelFileDocument($other, $this->documentType);
    Media::factory()->create([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $this->employee->getKey(),
        'collection_name' => 'documents',
    ]);

    $data = Livewire::test(Documents::class, ['employeeId' => $this->employee->getKey()])
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->all())->toBe([$own->getKey()]);
});

test('uploading files an approved document', function (): void {
    Livewire::test(Documents::class, ['employeeId' => $this->employee->getKey()])
        ->set('documentForm.document_type_id', $this->documentType->getKey())
        ->set('documentForm.document_date', '2025-03-10')
        ->set(
            'documentForm.media',
            UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.4\n%%EOF\n")
        )
        ->call('saveUpload')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $document = Media::query()->withoutGlobalScopes()->whereNotNull('document_type_id')->sole();

    expect($document->model_id)->toBe($this->employee->getKey())
        ->and($document->review_state)->toBe(DocumentReviewStateEnum::Approved)
        ->and($document->retain_until->toDateString())->toBe('2031-12-31');
});

test('a document of another employee cannot be downloaded from this tab', function (): void {
    $other = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Other',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
    $foreign = personnelFileDocument($other, $this->documentType);

    Livewire::test(Documents::class, ['employeeId' => $this->employee->getKey()])
        ->call('downloadDocument', $foreign->getKey())
        ->assertNotFound();
});

test('requires the tab permission', function (): void {
    Permission::findOrCreate('tab.employee.documents', 'web');
    $this->actingAs(User::factory()->create(['is_active' => true]), 'web');

    Livewire::test(Documents::class, ['employeeId' => $this->employee->getKey()])
        ->assertForbidden();
});

test('downloads a document of the employee', function (): void {
    $document = personnelFileDocument($this->employee, $this->documentType);
    Storage::disk('private')->put($document->getPathRelativeToRoot(), 'content');

    Livewire::test(Documents::class, ['employeeId' => $this->employee->getKey()])
        ->call('downloadDocument', $document->getKey())
        ->assertFileDownloaded($document->file_name);
});
