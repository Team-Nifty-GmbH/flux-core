<?php

use FluxErp\Enums\DocumentReviewStateEnum;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Livewire\Widgets\HumanResources\PersonnelFileStatusWidget;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Employee;
use FluxErp\Models\Media;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $this->contract = DocumentType::factory()->create([
        'name' => 'Employment contract',
        'model_types' => ['employee'],
        'is_required' => true,
    ]);
});

function employedEmployee(WorkTimeModel $workTimeModel, string $lastname): Employee
{
    $employee = app(Employee::class)->create([
        'tenant_id' => test()->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => $lastname,
        'is_active' => true,
        'employment_date' => now()->subYear(),
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $employee->getKey(),
        'work_time_model_id' => $workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);

    return $employee;
}

function personnelFileEntry(Employee $employee, DocumentType $documentType, array $attributes = []): Media
{
    return Media::factory()->create(array_merge([
        'model_type' => morph_alias(Employee::class),
        'model_id' => $employee->getKey(),
        'collection_name' => 'documents',
        'document_type_id' => $documentType->getKey(),
        'document_date' => '2025-03-10',
        'review_state' => DocumentReviewStateEnum::Approved,
    ], $attributes));
}

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(PersonnelFileStatusWidget::class)
        ->assertOk();
});

test('lists employed employees without an approved document of a required type', function (): void {
    DocumentType::factory()->create(['name' => 'Optional', 'model_types' => ['employee'], 'is_required' => false]);
    DocumentType::factory()->create(['name' => 'Contact only', 'model_types' => ['contact'], 'is_required' => true]);
    $complete = employedEmployee($this->workTimeModel, 'Complete');
    personnelFileEntry($complete, $this->contract);
    $pending = employedEmployee($this->workTimeModel, 'Pending');
    personnelFileEntry($pending, $this->contract, ['review_state' => DocumentReviewStateEnum::Pending]);
    $empty = employedEmployee($this->workTimeModel, 'Empty');

    $missing = Livewire::withoutLazyLoading()
        ->test(PersonnelFileStatusWidget::class)
        ->get('missingDocuments');

    expect(collect($missing)->pluck('document_types', 'id')->sortKeys()->all())->toBe([
        $pending->getKey() => ['Employment contract'],
        $empty->getKey() => ['Employment contract'],
    ]);
});

test('lists employees with documents whose retention has ended', function (): void {
    $employee = employedEmployee($this->workTimeModel, 'Expired');
    personnelFileEntry($employee, $this->contract, ['retain_until' => today()->subDay()]);
    personnelFileEntry($employee, $this->contract, ['retain_until' => today()->subYear()]);
    $locked = employedEmployee($this->workTimeModel, 'Locked');
    personnelFileEntry($locked, $this->contract, ['retain_until' => today()->addYear()]);

    $expired = Livewire::withoutLazyLoading()
        ->test(PersonnelFileStatusWidget::class)
        ->get('expiredDocuments');

    expect(collect($expired)->pluck('count', 'id')->all())->toBe([$employee->getKey() => 2]);
});
