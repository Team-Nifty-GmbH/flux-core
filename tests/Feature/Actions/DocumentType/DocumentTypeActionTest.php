<?php

use FluxErp\Actions\DocumentType\CreateDocumentType;
use FluxErp\Actions\DocumentType\DeleteDocumentType;
use FluxErp\Actions\DocumentType\UpdateDocumentType;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use Illuminate\Validation\ValidationException;

test('create document type', function (): void {
    $documentType = CreateDocumentType::make([
        'name' => 'Employment contract',
        'model_types' => [morph_alias(Employee::class)],
        'retention_years' => 6,
        'is_owner_uploadable' => true,
    ])->validate()->execute();

    expect($documentType)->toBeInstanceOf(DocumentType::class)
        ->name->toBe('Employment contract')
        ->model_types->toBe(['employee'])
        ->retention_years->toBe(6)
        ->is_owner_uploadable->toBeTrue()
        ->is_active->toBeTrue();
});

test('create document type requires a name', function (): void {
    CreateDocumentType::assertValidationErrors([], ['name']);
});

test('create document type refuses an unknown model type', function (): void {
    CreateDocumentType::assertValidationErrors(
        ['name' => 'Contract', 'model_types' => ['not_a_model']],
        ['model_types.0']
    );
});

test('update document type', function (): void {
    $documentType = DocumentType::factory()->create();

    $updated = UpdateDocumentType::make([
        'id' => $documentType->getKey(),
        'name' => 'Warning',
        'retention_years' => null,
    ])->validate()->execute();

    expect($updated->name)->toBe('Warning')
        ->and($updated->retention_years)->toBeNull();
});

test('delete unused document type', function (): void {
    $documentType = DocumentType::factory()->create();

    expect(DeleteDocumentType::make(['id' => $documentType->getKey()])->validate()->execute())
        ->toBeTrue()
        ->and(DocumentType::query()->whereKey($documentType->getKey())->exists())->toBeFalse();
});

test('delete document type refuses while a document uses it', function (): void {
    $documentType = DocumentType::factory()->create();
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
    Media::factory()->create([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'collection_name' => 'documents',
        'document_type_id' => $documentType->getKey(),
    ]);

    expect(fn () => DeleteDocumentType::make(['id' => $documentType->getKey()])->validate()->execute())
        ->toThrow(ValidationException::class);

    expect(DocumentType::query()->whereKey($documentType->getKey())->exists())->toBeTrue();
});

test('document type is allowed for every model without model types', function (): void {
    $documentType = DocumentType::factory()->create(['model_types' => null]);

    expect($documentType->isAllowedFor('employee'))->toBeTrue()
        ->and($documentType->isAllowedFor('contact'))->toBeTrue();
});

test('document type is allowed only for its model types', function (): void {
    $documentType = DocumentType::factory()->create(['model_types' => ['employee']]);

    expect($documentType->isAllowedFor('employee'))->toBeTrue()
        ->and($documentType->isAllowedFor('contact'))->toBeFalse();
});
