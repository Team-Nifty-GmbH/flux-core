<?php

use FluxErp\Actions\Document\ExportPayrollRecords;
use FluxErp\Actions\Document\UploadDocument;
use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['filesystems.disks.private' => ['driver' => 'local', 'root' => storage_path('app/private')]]);
    Storage::fake('private');
    Queue::fake();

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'employee_number' => 'P-17',
        'firstname' => 'Anna',
        'lastname' => 'Muster',
        'is_active' => true,
    ]);

    $this->contract = DocumentType::factory()->create([
        'name' => 'Employment contract',
        'model_types' => ['employee'],
        'retention_years' => 6,
        'is_payroll_record' => true,
    ]);
});

function payrollRecord(Employee $employee, DocumentType $documentType, array $data = []): Media
{
    return UploadDocument::make(array_merge([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'document_type_id' => $documentType->getKey(),
        'document_date' => '2025-03-10',
        'media' => UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.4\n%%EOF\n"),
    ], $data))
        ->validate()
        ->execute();
}

function exportPayrollRecordsZip(array $data): ZipArchive
{
    ob_start();
    ExportPayrollRecords::make($data)
        ->validate()
        ->execute()
        ->toResponse(request())
        ->sendContent();
    $path = tempnam(sys_get_temp_dir(), 'payroll');
    file_put_contents($path, ob_get_clean());

    $zip = new ZipArchive();
    $zip->open($path);

    return $zip;
}

function zipFileNames(ZipArchive $zip): array
{
    return collect(range(0, $zip->numFiles - 1))
        ->map(fn (int $index): string => $zip->getNameIndex($index))
        ->sort()
        ->values()
        ->all();
}

function exportIndexRows(ZipArchive $zip): array
{
    $lines = array_filter(explode("\n", str_replace("\u{FEFF}", '', $zip->getFromName('index.csv'))));

    return array_map(fn (string $line): array => str_getcsv($line, ';'), array_values($lines));
}

test('export files payroll records by year, document type and personnel number', function (): void {
    $media = payrollRecord($this->employee, $this->contract);

    $zip = exportPayrollRecordsZip(['from' => '2025-01-01', 'to' => '2025-12-31']);

    expect(zipFileNames($zip))->toBe(['2025/Employment contract/P-17/contract.pdf', 'index.csv'])
        ->and(exportIndexRows($zip)[1])->toBe([
            (string) $media->getKey(),
            'P-17',
            $this->employee->getLabel(),
            'Employment contract',
            '2025-03-10',
            '2025/Employment contract/P-17/contract.pdf',
            $media->checksum,
            'yes',
            '',
        ]);
});

test('export leaves out documents that are no payroll records, pending or outside the period', function (): void {
    $other = DocumentType::factory()->create(['model_types' => ['employee'], 'is_payroll_record' => false]);
    payrollRecord($this->employee, $other);
    payrollRecord($this->employee, $this->contract, ['document_date' => '2024-12-31']);
    payrollRecord($this->employee, $this->contract)
        ->forceFill(['review_state' => DocumentReviewStateEnum::Pending])
        ->save();
    $contact = Contact::factory()->create();
    payrollRecord($this->employee, $this->contract, ['document_date' => '2025-06-01'])
        ->forceFill(['model_type' => morph_alias(Contact::class), 'model_id' => $contact->getKey()])
        ->save();

    $zip = exportPayrollRecordsZip(['from' => '2025-01-01', 'to' => '2025-12-31']);

    expect(zipFileNames($zip))->toBe(['index.csv']);
});

test('export includes former employees and previous versions', function (): void {
    $previous = payrollRecord($this->employee, $this->contract);
    $version = payrollRecord($this->employee, $this->contract, [
        'previous_version_id' => $previous->getKey(),
        'document_date' => '2025-04-01',
        'media' => UploadedFile::fake()->createWithContent('contract-corrected.pdf', "%PDF-1.4\n%%EOF\n"),
    ]);
    $this->employee->delete();

    $rows = exportIndexRows(exportPayrollRecordsZip(['from' => '2025-01-01', 'to' => '2025-12-31']));

    expect(array_column(array_slice($rows, 1), 8, 5))->toBe([
        '2025/Employment contract/P-17/contract.pdf' => (string) $version->getKey(),
        '2025/Employment contract/P-17/contract-corrected.pdf' => '',
    ]);
});

test('export marks a changed file as not intact', function (): void {
    $media = payrollRecord($this->employee, $this->contract);
    file_put_contents($media->getPath(), 'tampered');

    $rows = exportIndexRows(exportPayrollRecordsZip(['from' => '2025-01-01', 'to' => '2025-12-31']));

    expect($rows[1][7])->toBe('no');
});

test('export refuses a period that ends before it starts', function (): void {
    ExportPayrollRecords::assertValidationErrors(['from' => '2025-12-31', 'to' => '2025-01-01'], ['to']);
});

test('export does not show a rejected version as replacement', function (): void {
    $previous = payrollRecord($this->employee, $this->contract);
    payrollRecord($this->employee, $this->contract, ['previous_version_id' => $previous->getKey()])
        ->forceFill(['review_state' => DocumentReviewStateEnum::Rejected])
        ->save();

    $rows = exportIndexRows(exportPayrollRecordsZip(['from' => '2025-01-01', 'to' => '2025-12-31']));

    expect(array_column(array_slice($rows, 1), 8, 0))->toBe([(string) $previous->getKey() => '']);
});

test('export leaves out payroll records of employees in tenants the user cannot see', function (): void {
    $otherEmployee = app(Employee::class)->create([
        'tenant_id' => Tenant::factory()->create()->getKey(),
        'firstname' => 'Other',
        'lastname' => 'Tenant',
        'is_active' => true,
    ]);
    payrollRecord($this->employee, $this->contract);
    payrollRecord($this->employee, $this->contract, [
        'media' => UploadedFile::fake()->createWithContent('foreign.pdf', "%PDF-1.4\n%%EOF\n"),
    ])
        ->forceFill(['model_id' => $otherEmployee->getKey()])
        ->save();
    $user = User::factory()->create(['is_active' => true]);
    $user->tenants()->attach($this->dbTenant->getKey());
    $this->actingAs($user, 'web');
    Context::flush();

    $zip = exportPayrollRecordsZip(['from' => '2025-01-01', 'to' => '2025-12-31']);

    expect(zipFileNames($zip))->toBe(['2025/Employment contract/P-17/contract.pdf', 'index.csv']);
});
