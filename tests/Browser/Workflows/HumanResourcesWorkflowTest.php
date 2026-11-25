<?php

use FluxErp\Actions\Document\UploadDocument;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Enums\SalaryTypeEnum;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Number;
use Spatie\Permission\PermissionRegistrar;

test('hr dashboard loads without js errors', function (): void {
    visit(route('human-resources.dashboard'))
        ->assertNoSmoke();
});

test('employees list loads without js errors', function (): void {
    visit(route('human-resources.employees'))
        ->assertNoSmoke();
});

test('employees list shows data table', function (): void {
    visit(route('human-resources.employees'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('work times page loads without js errors', function (): void {
    visit(route('human-resources.work-times'))
        ->assertNoSmoke();
});

test('work times shows data table', function (): void {
    visit(route('human-resources.work-times'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('absence requests page loads without js errors', function (): void {
    visit(route('human-resources.absence-requests'))
        ->assertNoSmoke();
});

test('absence requests shows data table', function (): void {
    visit(route('human-resources.absence-requests'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('attendance overview page loads without js errors', function (): void {
    visit(route('human-resources.attendance-overview'))
        ->assertNoSmoke();
});

test('employee days page loads without js errors', function (): void {
    visit(route('human-resources.employee-days'))
        ->assertNoSmoke();
});

test('employee days shows data table', function (): void {
    visit(route('human-resources.employee-days'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('salary tab saves the salary and a component and updates the costs', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'salary_type' => SalaryTypeEnum::Monthly,
        'base_salary' => 2000,
        'employer_contribution_rate' => 10,
    ]);

    $page = visit(route('human-resources.employees.id', ['id' => $employee->getKey(), 'tab' => 'employee.salary']))
        ->assertNoSmoke();

    $total = json_encode(Number::currency(2200));
    waitForCondition($page, <<<JS
        () => document.querySelector('[x-text="\$wire.costs.total"]')?.textContent.trim() === {$total}
    JS, 10000);

    $page->script(<<<'JS'
        () => {
            const component = Livewire.all().find((c) => c.name === 'employee.salary');
            component.$wire.employeeSalaryForm.base_salary = 2500;
            document.querySelector('[wire\\:click="saveSalary()"]').click();
        }
    JS);

    $total = json_encode(Number::currency(2750));
    waitForCondition($page, <<<JS
        () => document.querySelector('[x-text="\$wire.costs.total"]')?.textContent.trim() === {$total}
    JS, 10000);

    $page->script(<<<'JS'
        () => Livewire.all().find((c) => c.name === 'employee.salary').$wire.edit()
    JS);

    waitForCondition($page, <<<'JS'
        () => document.querySelector('#salary-component-form-modal')?.offsetParent !== null
    JS, 10000);

    $page->script(<<<'JS'
        () => {
            const wire = Livewire.all().find((c) => c.name === 'employee.salary').$wire;
            wire.salaryComponentForm.name = 'Allowance';
            wire.salaryComponentForm.amount = 100;
            wire.salaryComponentForm.payment_interval = 'monthly';
            document.querySelector('#salary-component-form-modal [x-on\\:click*="$wire.save()"]').click();
        }
    JS);

    $gross = json_encode(Number::currency(2600));
    waitForCondition($page, <<<JS
        () => document.querySelector('#salary-component-form-modal')?.offsetParent === null
            && document.querySelector('[x-text="\$wire.costs.gross"]')?.textContent.trim() === {$gross}
    JS, 10000);

    $page->assertNoJavascriptErrors();

    expect(bccomp($employee->refresh()->base_salary, '2500', 2))->toBe(0)
        ->and($employee->salaryComponents()->where('name', 'Allowance')->exists())->toBeTrue();
});

test('personnel file takes an employee upload for review and locks it after approval', function (): void {
    config(['filesystems.disks.private' => ['driver' => 'local', 'root' => storage_path('app/private')]]);
    Queue::fake();

    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    $settings = visit(route('settings.document-types'))->assertNoSmoke();
    $settings->script(<<<'JS'
        () => Livewire.all().find((c) => c.name === 'settings.document-types').$wire.edit()
    JS);
    waitForCondition($settings, <<<'JS'
        () => document.querySelector('#edit-document-type-modal')?.offsetParent !== null
    JS, 10000);
    $settings->script(<<<'JS'
        () => {
            const wire = Livewire.all().find((c) => c.name === 'settings.document-types').$wire;
            wire.documentTypeForm.name = 'Certificate';
            wire.documentTypeForm.model_types = ['employee'];
            wire.documentTypeForm.retention_years = 6;
            wire.documentTypeForm.is_owner_uploadable = true;
            document.querySelector('#edit-document-type-modal [x-on\\:click*="$wire.save()"]').click();
        }
    JS);
    waitForCondition($settings, <<<'JS'
        () => document.querySelector('#edit-document-type-modal')?.offsetParent === null
    JS, 10000);

    $documentType = DocumentType::query()->where('name', 'Certificate')->sole();

    Permission::findOrCreate('action.document.review', 'web');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    UploadDocument::make([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'document_type_id' => $documentType->getKey(),
        'document_date' => '2025-03-10',
        'media' => UploadedFile::fake()->createWithContent('certificate.pdf', "%PDF-1.4\n%%EOF\n"),
    ])
        ->validate()
        ->execute();

    $profile = visit(route('human-resources.my-employee-profile', ['tab' => 'my-employee-profile.documents']))
        ->assertNoSmoke();
    waitForDataTable($profile);
    $profile->assertSee('certificate.pdf')
        ->assertNoJavascriptErrors();

    $document = Media::query()->withoutGlobalScopes()->whereNotNull('document_type_id')->sole();
    expect($document->review_state)->toBe(DocumentReviewStateEnum::Pending);

    $this->user->givePermissionTo('action.document.review');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $file = visit(route('human-resources.employees.id', ['id' => $employee->getKey(), 'tab' => 'employee.documents']))
        ->assertNoSmoke();
    waitForDataTable($file);
    $file->script(<<<JS
        () => Livewire.all().find((c) => c.name === 'employee.documents').\$wire.review({$document->getKey()})
    JS);
    waitForCondition($file, <<<'JS'
        () => document.querySelector('#review-document-modal')?.offsetParent !== null
    JS, 10000);
    $file->script(<<<'JS'
        () => document.querySelector('#review-document-modal [x-on\\:click*="saveReview(\'approved\')"]').click()
    JS);
    waitForCondition($file, <<<'JS'
        () => document.querySelector('#review-document-modal')?.offsetParent === null
    JS, 10000);

    expect($document->refresh()->review_state)->toBe(DocumentReviewStateEnum::Approved)
        ->and($document->retain_until->toDateString())->toBe('2031-12-31');

    waitForCondition($file, <<<'JS'
        () => {
            const buttons = [...document.querySelectorAll('[wire\\:click^="deleteDocument"]')];

            return buttons.length > 0 && buttons.every((button) => button.offsetParent === null);
        }
    JS, 10000);

    $file->assertNoJavascriptErrors();
});
