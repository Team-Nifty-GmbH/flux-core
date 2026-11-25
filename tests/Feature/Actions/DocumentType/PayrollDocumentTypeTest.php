<?php

use FluxErp\Actions\DocumentType\CreateDocumentType;
use FluxErp\Actions\DocumentType\CreatePayrollDocumentTypes;
use FluxErp\Actions\DocumentType\UpdateDocumentType;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Language;
use FluxErp\Models\Media;

test('create payroll document types adds the payroll records for employees', function (): void {
    CreatePayrollDocumentTypes::make()->validate()->execute();

    $documentTypes = DocumentType::query()->get();

    expect($documentTypes)->toHaveCount(11)
        ->and($documentTypes->pluck('name')->all())->toContain(
            'Employment contract',
            'Working time records (Minimum Wage Act)',
            'Proof of health insurance'
        )
        ->and($documentTypes->every(fn (DocumentType $documentType): bool => $documentType->is_payroll_record
            && $documentType->is_active
            && $documentType->retention_years === 6
            && $documentType->model_types === ['employee']
        ))->toBeTrue();
});

test('create payroll document types keeps existing types untouched', function (): void {
    $existing = DocumentType::factory()->create([
        'name' => 'Employment contract',
        'retention_years' => 10,
        'is_payroll_record' => true,
    ]);

    CreatePayrollDocumentTypes::make()->validate()->execute();
    CreatePayrollDocumentTypes::make()->validate()->execute();

    expect(DocumentType::query()->count())->toBe(11)
        ->and(DocumentType::query()->where('name', 'Employment contract')->sole()->retention_years)->toBe(10)
        ->and($existing->fresh()->retention_years)->toBe(10);
});

test('create payroll document types does not duplicate the types when run in another locale', function (): void {
    Language::factory()->create(['language_code' => 'de']);

    app()->setLocale('de');
    CreatePayrollDocumentTypes::make()->validate()->execute();

    app()->setLocale('en');
    CreatePayrollDocumentTypes::make()->validate()->execute();

    expect(DocumentType::query()->count())->toBe(11)
        ->and(DocumentType::query()->where('name', 'Arbeitsvertrag')->exists())->toBeTrue();
});

test('create document type refuses a payroll record without retention', function (): void {
    CreateDocumentType::assertValidationErrors(
        ['name' => 'Employment contract', 'is_payroll_record' => true],
        ['retention_years']
    );

    expect(DocumentType::query()->exists())->toBeFalse();
});

test('create document type accepts a payroll record with retention', function (): void {
    $documentType = CreateDocumentType::make([
        'name' => 'Employment contract',
        'is_payroll_record' => true,
        'retention_years' => 6,
    ])->validate()->execute();

    expect($documentType->is_payroll_record)->toBeTrue()
        ->and($documentType->retention_years)->toBe(6);
});

test('update document type refuses to drop the retention of a payroll record', function (): void {
    $documentType = DocumentType::factory()->create(['is_payroll_record' => true, 'retention_years' => 6]);

    UpdateDocumentType::assertValidationErrors(
        ['id' => $documentType->getKey(), 'retention_years' => null],
        ['retention_years']
    );

    expect($documentType->fresh()->retention_years)->toBe(6);
});

test('update document type refuses to mark a type without retention as payroll record', function (): void {
    $documentType = DocumentType::factory()->create(['retention_years' => null]);

    UpdateDocumentType::assertValidationErrors(
        ['id' => $documentType->getKey(), 'is_payroll_record' => true],
        ['retention_years']
    );

    expect($documentType->fresh()->is_payroll_record)->toBeFalse();
});

test('update document type locks the approved documents once the type gets a retention period', function (): void {
    $documentType = DocumentType::factory()->create(['model_types' => ['employee'], 'retention_years' => null]);
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
    $document = Media::factory()->create([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'collection_name' => 'documents',
        'document_type_id' => $documentType->getKey(),
        'document_date' => '2025-03-10',
        'review_state' => DocumentReviewStateEnum::Approved,
    ]);

    UpdateDocumentType::make([
        'id' => $documentType->getKey(),
        'is_payroll_record' => true,
        'retention_years' => 6,
    ])->validate()->execute();

    $document = Media::query()->documents()->whereKey($document->getKey())->sole();

    expect($document->retain_until->toDateString())->toBe('2031-12-31')
        ->and($document->isLocked())->toBeTrue();
});
