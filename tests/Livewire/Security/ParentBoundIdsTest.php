<?php

use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeCanCreateEnum;
use FluxErp\Livewire\AbsenceRequest\EmployeeDays as AbsenceRequestEmployeeDays;
use FluxErp\Livewire\Accounting\Transactions\Comments as TransactionComments;
use FluxErp\Livewire\Address\Tasks as AddressTasks;
use FluxErp\Livewire\Contact\Accounting\BankConnections;
use FluxErp\Livewire\Contact\Accounting\CreditAccounts;
use FluxErp\Livewire\Contact\Communication as ContactCommunication;
use FluxErp\Livewire\Contact\WorkTimes as ContactWorkTimes;
use FluxErp\Livewire\Employee\Attachments as EmployeeAttachments;
use FluxErp\Livewire\Employee\Comments as EmployeeComments;
use FluxErp\Livewire\EmployeeDay\AbsenceRequests as EmployeeDayAbsenceRequests;
use FluxErp\Livewire\EmployeeDay\Activities as EmployeeDayActivities;
use FluxErp\Livewire\EmployeeDay\WorkTimes as EmployeeDayWorkTimes;
use FluxErp\Livewire\Lead\Tasks as LeadTasks;
use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Livewire\Project\WorkTimes as ProjectWorkTimes;
use FluxErp\Livewire\SignaturePublicLink\Comments as SignatureComments;
use FluxErp\Livewire\Ticket\Comments as TicketComments;
use FluxErp\Livewire\Widgets\MyLeadWonLostRatio;
use FluxErp\Livewire\Widgets\UserLeadWonLostRatio;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Employee;
use FluxErp\Models\Permission;
use FluxErp\Models\Tenant;
use FluxErp\Models\Ticket;
use FluxErp\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Foreign',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);
});

test('page bound ids can not be changed from the client', function (string $component, string $property): void {
    Livewire::test($component, [$property => 0])
        ->set($property, 2);
})
    ->with([
        [AddressTasks::class, 'modelId'],
        [BankConnections::class, 'contactId'],
        [ContactCommunication::class, 'modelId'],
        [ContactWorkTimes::class, 'contactId'],
        [CreditAccounts::class, 'contactId'],
        [EmployeeDayAbsenceRequests::class, 'employeeDayId'],
        [EmployeeDayActivities::class, 'modelId'],
        [EmployeeDayWorkTimes::class, 'employeeDayId'],
        [LeadTasks::class, 'modelId'],
        [MyLeadWonLostRatio::class, 'userId'],
        [ProjectTaskList::class, 'projectId'],
        [ProjectWorkTimes::class, 'projectId'],
    ])
    ->throws(CannotUpdateLockedPropertyException::class);

test('the absence request of the employee days can not be changed from the client', function (): void {
    $absenceType = app(AbsenceType::class)->create([
        'name' => 'Sick',
        'code' => 'SIC',
        'color' => '#ef4444',
        'employee_can_create' => EmployeeCanCreateEnum::Yes,
        'affects_overtime' => true,
        'affects_sick_leave' => true,
        'affects_vacation' => false,
    ]);
    $absenceRequests = collect([now(), now()->addWeek()])
        ->map(fn (Carbon $date): AbsenceRequest => app(AbsenceRequest::class)->create([
            'employee_id' => $this->employee->getKey(),
            'absence_type_id' => $absenceType->getKey(),
            'start_date' => $date,
            'end_date' => $date,
            'day_part' => AbsenceRequestDayPartEnum::FullDay,
            'state' => AbsenceRequestStateEnum::Approved,
        ]));

    Livewire::test(AbsenceRequestEmployeeDays::class, ['absenceRequestId' => $absenceRequests[0]->getKey()])
        ->set('absenceRequestId', $absenceRequests[1]->getKey());
})->throws(CannotUpdateLockedPropertyException::class);

test('the user lead ratio keeps its user selection', function (): void {
    $this->actingAs($this->user, 'web');

    Livewire::test(UserLeadWonLostRatio::class)
        ->set('userId', $this->user->getKey())
        ->assertSet('userId', $this->user->getKey());
});

test('a guest on a public link can not bind another record', function (): void {
    Auth::logout();

    Livewire::test(SignatureComments::class)
        ->set('modelId', 1);
})->throws(CannotUpdateLockedPropertyException::class);

test('an address can not bind another record', function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(Address::class),
        'authenticatable_id' => $address->getKey(),
    ]);

    $this->actingAs($address, 'web');

    Livewire::test(TicketComments::class)
        ->set('modelId', $ticket->getKey());
})->throws(CannotUpdateLockedPropertyException::class);

test('a user without the detail route can not bind a record', function (string $component): void {
    Permission::findOrCreate(route_to_permission('human-resources.employees.id', false), 'web');

    Livewire::test($component)
        ->set('modelId', $this->employee->getKey());
})
    ->with([EmployeeAttachments::class, EmployeeComments::class])
    ->throws(CannotUpdateLockedPropertyException::class);

test('a user with the detail route can swap the record', function (string $component): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate(route_to_permission('human-resources.employees.id', false), 'web')
    );

    Livewire::test($component)
        ->set('modelId', $this->employee->getKey())
        ->assertSet('modelId', $this->employee->getKey())
        ->set('modelId', null)
        ->assertSet('modelId', null);
})->with([EmployeeAttachments::class, EmployeeComments::class]);

test('a record of a foreign tenant can not be bound', function (): void {
    $otherTenant = Tenant::factory()->create();
    $employee = app(Employee::class)->create([
        'tenant_id' => $otherTenant->getKey(),
        'firstname' => 'Other',
        'lastname' => 'Tenant',
        'is_active' => true,
    ]);

    $this->user->tenants()->sync([$this->dbTenant->getKey()]);
    Context::forget('user_tenant_ids');

    Livewire::test(EmployeeComments::class)
        ->set('modelId', $employee->getKey());
})->throws(CannotUpdateLockedPropertyException::class);

test('a model without a detail route can be swapped', function (): void {
    $transaction = Transaction::factory()->create();

    Livewire::test(TransactionComments::class)
        ->set('modelId', $transaction->getKey())
        ->assertSet('modelId', $transaction->getKey());
});

test('the folder tree can not be pointed at another record through a method', function (): void {
    Livewire::test(EmployeeAttachments::class)
        ->call('loadModel', ['modelId' => $this->employee->getKey()]);
})->throws(MethodNotFoundException::class);
