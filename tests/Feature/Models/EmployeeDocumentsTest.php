<?php

use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('employee documents are stored on the private local disk', function (): void {
    Storage::fake('local');
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Erika',
        'lastname' => 'Mustermann',
    ]);

    $media = UploadMedia::make([
        'model_type' => $employee->getMorphClass(),
        'model_id' => $employee->getKey(),
        'collection_name' => 'documents',
        'media' => UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.4\n%%EOF\n"),
    ])
        ->validate()
        ->execute();

    expect($media->disk)->toBe('local');
    Storage::disk('local')->assertExists($media->getPathRelativeToRoot());
});
