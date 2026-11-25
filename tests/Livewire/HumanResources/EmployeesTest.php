<?php

use FluxErp\Actions\Document\UploadDocument;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\HumanResources\Employees;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Employees::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Employees::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('employeeForm.id', null)
        ->assertSet('employeeForm.firstname', null)
        ->assertSet('employeeForm.lastname', null)
        ->assertOpensModal('employee-form-modal');
});

test('edit with id redirects to detail route', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(Employees::class)
        ->call('edit', $employee->getKey())
        ->assertRedirect();
});

test('can create employee via save', function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 24,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $newUser = FluxErp\Models\User::factory()->create([
        'is_active' => true,
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    Livewire::test(Employees::class)
        ->call('edit')
        ->set('employeeForm.firstname', 'New')
        ->set('employeeForm.lastname', 'Employee')
        ->set('employeeForm.tenant_id', $this->dbTenant->getKey())
        ->set('employeeForm.user_id', $newUser->getKey())
        ->set('employeeForm.employment_date', now()->format('Y-m-d'))
        ->set('employeeForm.work_time_model_id', $workTimeModel->getKey())
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('employees', [
        'firstname' => 'New',
        'lastname' => 'Employee',
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
});

test('save validates required fields', function (): void {
    Livewire::test(Employees::class)
        ->call('edit')
        ->set('employeeForm.firstname', null)
        ->set('employeeForm.lastname', null)
        ->call('save')
        ->assertReturned(false);
});

test('downloads the documents of the selected employees as zip', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    Livewire::test(Employees::class)
        ->set('selected', [$employee->getKey()])
        ->call('downloadDocuments')
        ->assertFileDownloaded('documents.zip');
});

test('export payroll records streams a zip for the period', function (): void {
    config(['filesystems.disks.private' => ['driver' => 'local', 'root' => storage_path('app/private')]]);
    Storage::fake('private');
    Queue::fake();
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'employee_number' => 'P-17',
        'firstname' => 'Anna',
        'lastname' => 'Muster',
        'is_active' => true,
    ]);
    $contract = DocumentType::factory()->create([
        'name' => 'Contract',
        'model_types' => ['employee'],
        'is_payroll_record' => true,
    ]);
    foreach (['inside.pdf' => '2025-03-10', 'outside.pdf' => '2024-12-31'] as $fileName => $documentDate) {
        UploadDocument::make([
            'model_type' => morph_alias(Employee::class),
            'model_id' => $employee->getKey(),
            'document_type_id' => $contract->getKey(),
            'document_date' => $documentDate,
            'media' => UploadedFile::fake()->createWithContent($fileName, "%PDF-1.4\n%%EOF\n"),
        ])
            ->validate()
            ->execute();
    }

    $component = Livewire::test(Employees::class)
        ->set('payrollRecordsPeriod', ['2025-01-01', '2025-12-31'])
        ->call('exportPayrollRecords')
        ->assertHasNoErrors()
        ->assertFileDownloaded('payroll-records.zip');

    $path = tempnam(sys_get_temp_dir(), 'payroll');
    file_put_contents($path, base64_decode(data_get($component->effects, 'download.content')));
    $zip = new ZipArchive();
    $zip->open($path);

    expect($zip->locateName('2025/Contract/P-17/inside.pdf'))->not->toBeFalse()
        ->and($zip->numFiles)->toBe(2);
});

test('export payroll records refuses a period without an end', function (): void {
    Livewire::test(Employees::class)
        ->set('payrollRecordsPeriod', ['2025-01-01'])
        ->call('exportPayrollRecords')
        ->assertNoFileDownloaded();
});
