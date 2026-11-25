<?php

use FluxErp\Livewire\Settings\DocumentTypes;
use FluxErp\Models\DocumentType;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DocumentTypes::class)
        ->assertOk();
});

test('saving the form creates a document type', function (): void {
    Livewire::test(DocumentTypes::class)
        ->set('documentTypeForm.name', 'Employment contract')
        ->set('documentTypeForm.model_types', ['employee'])
        ->set('documentTypeForm.retention_years', 6)
        ->set('documentTypeForm.is_owner_uploadable', true)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $documentType = DocumentType::query()->where('name', 'Employment contract')->sole();

    expect($documentType->model_types)->toBe(['employee'])
        ->and($documentType->retention_years)->toBe(6)
        ->and($documentType->is_owner_uploadable)->toBeTrue()
        ->and($documentType->is_active)->toBeTrue();
});

test('saving the form marks a document type as payroll record', function (): void {
    Livewire::test(DocumentTypes::class)
        ->set('documentTypeForm.name', 'Employment contract')
        ->set('documentTypeForm.retention_years', 6)
        ->set('documentTypeForm.is_payroll_record', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect(DocumentType::query()->where('name', 'Employment contract')->sole()->is_payroll_record)->toBeTrue();
});

test('saving a payroll record without retention is refused', function (): void {
    Livewire::test(DocumentTypes::class)
        ->set('documentTypeForm.name', 'Employment contract')
        ->set('documentTypeForm.is_payroll_record', true)
        ->call('save')
        ->assertHasErrors(['documentTypeForm.retention_years'])
        ->assertReturned(false);

    expect(DocumentType::query()->exists())->toBeFalse();
});

test('add payroll document types creates the payroll records', function (): void {
    Livewire::test(DocumentTypes::class)
        ->call('createPayrollDocumentTypes')
        ->assertHasNoErrors();

    expect(DocumentType::query()->where('is_payroll_record', true)->count())->toBe(11);
});
