<?php

use FluxErp\Livewire\Widgets\Contact\OpenItems;
use FluxErp\Livewire\Widgets\Contact\PaymentBehavior;
use FluxErp\Livewire\Widgets\Employee\VacationYearlyOverview;
use FluxErp\Livewire\Widgets\HumanResources\VacationHeatmapWidget;
use FluxErp\Livewire\Widgets\MyOverdueTasks;
use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Livewire\Widgets\Outstanding;
use FluxErp\Livewire\Widgets\RecurringRevenueForecast;
use FluxErp\Livewire\Widgets\Revenue;
use FluxErp\Models\Employee;
use FluxErp\Models\Order;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

class LiveUpdatesExtendedRevenue extends Revenue
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
}

function widgetListeners(string $component, array $params = []): array
{
    $instance = Livewire::test($component, $params)->instance();

    return (fn (): array => $this->getListeners())->call($instance);
}

test('a widget listens on the shared channel of the model it declares', function (): void {
    $this->actingAs($this->user);

    expect(widgetListeners(Revenue::class))->toBe([
        'echo-private:' . morph_alias(Order::class) . '.,.OrderLocked' => 'calculateByTimeFrame',
    ]);
});

test('a personal widget listens only on the channel of the current user', function (): void {
    $this->actingAs($this->user);

    $listeners = widgetListeners(MyTasks::class);

    expect(array_keys($listeners))->toContain(
        'echo-private:user.' . $this->user->getKey() . ',.TaskUpdated',
        'echo-private:user.' . $this->user->getKey() . ',.TaskUserCreated',
        'echo-private:user.' . $this->user->getKey() . ',.TaskUserDeleted',
    )
        ->and(array_filter(
            array_keys($listeners),
            fn (string $key): bool => ! str_starts_with($key, 'echo-private:user.' . $this->user->getKey() . ',')
        ))
        ->toBeEmpty();
});

test('a contact widget listens only on the channel of its contact', function (): void {
    $this->actingAs($this->user);

    $listeners = widgetListeners(OpenItems::class, ['contactId' => 42]);

    expect($listeners)->toHaveKey('echo-private:contact.42,.OrderUpdated')
        ->and(array_filter(
            array_keys($listeners),
            fn (string $key): bool => ! str_starts_with($key, 'echo-private:contact.42,')
        ))
        ->toBeEmpty();
});

test('a widget can extend the declared live updates', function (): void {
    $this->actingAs($this->user);

    expect(widgetListeners(LiveUpdatesExtendedRevenue::class))->toBe([
        'echo-private:' . morph_alias(Order::class) . '.,.OrderLocked' => 'calculateByTimeFrame',
        'echo-private:' . morph_alias(Ticket::class) . '.,.TicketCreated' => 'calculateByTimeFrame',
    ]);
});

test('a date dependent widget schedules a reload at midnight', function (): void {
    $this->actingAs($this->user);

    $script = data_get(Livewire::test(MyOverdueTasks::class)->effects, 'xjs.0.expression');

    expect($script)->toContain('setHours(24', "\$wire.call('\$refresh')");
});

test('a widget whose data depends on the date reloads at midnight', function (string $component): void {
    $this->actingAs($this->user);

    expect(data_get(Livewire::test($component)->effects, 'xjs.0.expression'))->toContain('setHours(24');
})->with([VacationHeatmapWidget::class]);

test('a widget declares the order events its data depends on', function (string $component, array $params, string $channel, array $events): void {
    $this->actingAs($this->user);

    $listeners = widgetListeners($component, $params);

    foreach ($events as $event) {
        expect($listeners)->toHaveKey('echo-private:' . $channel . ',.' . $event);
    }
})->with([
    'outstanding' => [Outstanding::class, [], 'order.', ['OrderCreated', 'OrderUpdated', 'OrderDeleted']],
    'open items' => [OpenItems::class, ['contactId' => 42], 'contact.42', ['OrderCreated', 'OrderUpdated', 'OrderDeleted']],
    'payment behavior' => [PaymentBehavior::class, ['contactId' => 42], 'contact.42', ['OrderCreated', 'OrderUpdated', 'OrderDeleted']],
    'recurring revenue forecast' => [RecurringRevenueForecast::class, [], 'order.', ['OrderUpdated', 'OrderDeleted']],
    'vacation heatmap' => [VacationHeatmapWidget::class, [], 'employee.', ['EmployeeCreated', 'EmployeeUpdated', 'EmployeeDeleted']],
]);

test('a widget that calculates its data on mount reloads through that calculation', function (): void {
    $this->actingAs($this->user);

    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'employment_date' => now()->subYear(),
        'is_active' => true,
    ]);

    expect(array_values(array_unique(widgetListeners(VacationYearlyOverview::class, ['employeeId' => $employee->getKey()]))))
        ->toBe(['calculateYearlyData']);
});

test('a widget that does not depend on the date schedules no reload', function (): void {
    $this->actingAs($this->user);

    expect(Livewire::test(Revenue::class)->effects['xjs'] ?? [])->toBeEmpty();
});
