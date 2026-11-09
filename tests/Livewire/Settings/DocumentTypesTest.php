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
