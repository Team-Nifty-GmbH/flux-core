<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\Calendar;
use FluxErp\Livewire\Widgets\Contact\OpenItems;
use FluxErp\Livewire\Widgets\Contact\Orders;
use FluxErp\Livewire\Widgets\Contact\PaymentBehavior;
use FluxErp\Livewire\Widgets\Contact\RevenueOverTime;
use FluxErp\Livewire\Widgets\Employee\VacationYearlyOverview;
use FluxErp\Livewire\Widgets\HumanResources\VacationHeatmapWidget;
use FluxErp\Livewire\Widgets\MyOverdueTasks;
use FluxErp\Livewire\Widgets\MyTargetProgresses;
use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Livewire\Widgets\Outstanding;
use FluxErp\Livewire\Widgets\RecurringRevenueForecast;
use FluxErp\Livewire\Widgets\Revenue;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Employee;
use FluxErp\Models\Order;
use FluxErp\Models\Target;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs($this->user);

    $this->subscription = fn (Testable $component): ?array => collect(data_get($component->effects, 'xjs', []))
        ->pluck('expression')
        ->filter(fn (string $expression): bool => str_starts_with($expression, 'window.fluxWidgetLiveUpdates('))
        ->map(fn (string $expression): array => json_decode(
            Str::beforeLast(Str::after($expression, '$wire, '), ')'),
            true
        ))
        ->last();

    $this->listeners = fn (string $component, array $params = []): array => data_get(
        ($this->subscription)(Livewire::test($component, $params)),
        'listeners',
        []
    );
});

test('a widget subscribes to the shared channel of the model it declares', function (): void {
    expect(($this->subscription)(Livewire::test(Revenue::class)))->toMatchArray([
        'method' => 'calculateByTimeFrame',
        'listeners' => [[morph_alias(Order::class) . '.', '.OrderLocked']],
    ]);
});

test('a personal widget subscribes only to the channel of the current user', function (): void {
    $listeners = ($this->listeners)(MyTasks::class);
    $userChannel = 'user.' . $this->user->getKey();

    expect($listeners)->toContain(
        [$userChannel, '.TaskUpdated'],
        [$userChannel, '.TaskUserCreated'],
        [$userChannel, '.TaskUserDeleted'],
    )
        ->and(collect($listeners)->pluck(0)->unique()->values()->all())->toBe([$userChannel]);
});

test('a widget listening to deletions also listens to restores', function (): void {
    expect(($this->listeners)(MyTasks::class))
        ->toContain(['user.' . $this->user->getKey(), '.TaskRestored']);
});

test('a contact widget subscribes only to the channel of its contact', function (): void {
    $listeners = ($this->listeners)(OpenItems::class, ['contactId' => 42]);

    expect($listeners)->toContain(['contact.42', '.OrderUpdated'])
        ->and(collect($listeners)->pluck(0)->unique()->values()->all())->toBe(['contact.42']);
});

test('a contact widget without a contact subscribes to nothing', function (string $component): void {
    expect(($this->listeners)($component))->toBe([]);
})->with([OpenItems::class, Orders::class, PaymentBehavior::class, RevenueOverTime::class]);

test('a widget can extend the declared live updates', function (): void {
    $component = new class() extends Revenue
    {
        protected function liveUpdates(): array
        {
            return array_merge_recursive(
                parent::liveUpdates(),
                [
                    $this->modelChannel(Ticket::class) => [Ticket::class => ['created']],
                ]
            );
        }
    };

    expect(($this->listeners)($component::class))->toBe([
        [morph_alias(Order::class) . '.', '.OrderLocked'],
        [morph_alias(Ticket::class) . '.', '.TicketCreated'],
    ]);
});

test('a widget declares the order events its data depends on', function (string $component, array $params, string $channel, array $events): void {
    $listeners = ($this->listeners)($component, $params);

    foreach ($events as $event) {
        expect($listeners)->toContain([$channel, '.' . $event]);
    }
})->with([
    'outstanding' => [Outstanding::class, [], 'order.', ['OrderCreated', 'OrderUpdated', 'OrderDeleted']],
    'open items' => [OpenItems::class, ['contactId' => 42], 'contact.42', ['OrderCreated', 'OrderUpdated', 'OrderDeleted']],
    'payment behavior' => [PaymentBehavior::class, ['contactId' => 42], 'contact.42', ['OrderCreated', 'OrderUpdated', 'OrderDeleted']],
    'recurring revenue forecast' => [RecurringRevenueForecast::class, [], 'order.', ['OrderUpdated', 'OrderDeleted']],
    'vacation heatmap' => [VacationHeatmapWidget::class, [], 'employee.', ['EmployeeCreated', 'EmployeeUpdated', 'EmployeeDeleted']],
]);

test('the calendar widget also refetches when a calendarable record changes', function (): void {
    expect(($this->listeners)(Calendar::class))->toContain(
        [morph_alias(Task::class) . '.', '.TaskUpdated'],
        [morph_alias(CalendarEvent::class) . '.', '.CalendarEventCreated'],
    );
});

test('the target progress widget listens to the model its target measures', function (): void {
    $target = Target::query()->create([
        'name' => 'Revenue',
        'target_value' => 1000,
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'model_type' => morph_alias(Order::class),
        'timeframe_column' => 'invoice_date',
        'aggregate_type' => 'sum',
        'aggregate_column' => 'total_net_price',
        'owner_column' => 'agent_id',
    ]);

    expect(($this->listeners)(MyTargetProgresses::class, ['targetId' => $target->getKey()]))->toContain(
        [morph_alias(Target::class) . '.', '.TargetUpdated'],
        [morph_alias(Order::class) . '.', '.OrderUpdated'],
    );
});

test('a widget that calculates its data on mount reloads through that calculation', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'employment_date' => now()->subYear(),
        'is_active' => true,
    ]);

    expect(data_get(
        ($this->subscription)(Livewire::test(VacationYearlyOverview::class, ['employeeId' => $employee->getKey()])),
        'method'
    ))->toBe('calculateYearlyData');
});

test('a date dependent widget reloads shortly after midnight of the app timezone', function (string $component): void {
    Carbon::setTestNow(Carbon::parse('2026-10-14 23:59:00'));

    expect(data_get(($this->subscription)(Livewire::test($component)), 'midnightIn'))
        ->toBeGreaterThanOrEqual(65000)
        ->toBeLessThanOrEqual(90000);
})->with([MyOverdueTasks::class, VacationHeatmapWidget::class, Revenue::class]);

test('a widget that does not depend on the date schedules no reload', function (): void {
    expect(data_get(($this->subscription)(Livewire::test(MyTasks::class)), 'midnightIn'))->toBeNull();
});

test('revenue shows a newly locked invoice after its live update', function (): void {
    $component = Livewire::test(Revenue::class);
    $before = $component->get('sum');
    $method = data_get(($this->subscription)($component), 'method');

    makeForecastOrder(OrderTypeEnum::Order, '1234.50')->update([
        'invoice_date' => now(),
        'invoice_number' => 'RE-LIVE-1',
        'is_locked' => true,
    ]);

    $component->call($method);

    expect($component->get('sum'))->not->toBe($before)->toStartWith('1.23K');
});

test('my tasks shows a task after the current user is assigned to it', function (): void {
    $task = Task::factory()->create(['name' => 'Assigned while watching', 'state' => 'open']);
    $component = Livewire::test(MyTasks::class)->assertDontSee('Assigned while watching');
    $method = data_get(($this->subscription)($component), 'method');

    $task->users()->attach($this->user);

    $component->call($method)->assertSee('Assigned while watching');
});
