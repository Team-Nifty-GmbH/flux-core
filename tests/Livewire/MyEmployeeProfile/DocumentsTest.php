<?php

use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Livewire\MyEmployeeProfile\Documents;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Permission;
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
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
    $this->other = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Other',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $this->documentType = DocumentType::factory()->create([
        'name' => 'Certificate',
        'model_types' => ['employee'],
        'retention_years' => 6,
        'is_owner_uploadable' => true,
        'is_active' => true,
    ]);
});

function myProfileDocument(Employee $employee, DocumentType $documentType): Media
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
    Livewire::test(Documents::class)
        ->assertOk();
});

test('shows only the own documents including the ones filed by hr', function (): void {
    $own = myProfileDocument($this->employee, $this->documentType);
    myProfileDocument($this->other, $this->documentType);

    $data = Livewire::test(Documents::class, ['employeeId' => $this->other->getKey()])
        ->assertSet('modelId', $this->employee->getKey())
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->all())->toBe([$own->getKey()]);
});

test('an own upload waits for review', function (): void {
    Permission::findOrCreate('action.document.review', 'web');
    Permission::findOrCreate('action.document.upload', 'web');

    Livewire::test(Documents::class)
        ->set('documentForm.document_type_id', $this->documentType->getKey())
        ->set('documentForm.document_date', '2025-03-10')
        ->set(
            'documentForm.media',
            UploadedFile::fake()->createWithContent('certificate.pdf', "%PDF-1.4\n%%EOF\n")
        )
        ->call('saveUpload')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $document = Media::query()->withoutGlobalScopes()->whereNotNull('document_type_id')->sole();

    expect($document->model_id)->toBe($this->employee->getKey())
        ->and($document->review_state)->toBe(DocumentReviewStateEnum::Pending)
        ->and($document->retain_until)->toBeNull();
});

test('offers only document types the employee may upload', function (): void {
    DocumentType::factory()->create([
        'name' => 'Warning',
        'model_types' => ['employee'],
        'is_owner_uploadable' => false,
        'is_active' => true,
    ]);

    Livewire::test(Documents::class)
        ->assertViewHas('documentTypes', [['label' => 'Certificate', 'value' => $this->documentType->getKey()]]);
});
