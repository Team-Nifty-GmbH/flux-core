<?php

use FluxErp\Livewire\Widgets\Contact\OpenItems;
use FluxErp\Livewire\Widgets\MyOverdueTasks;
use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Livewire\Widgets\Revenue;
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

    expect(json_encode(Livewire::test(MyOverdueTasks::class)->effects['xjs'] ?? []))
        ->toContain('setHours(24');
});

test('a widget that does not depend on the date schedules no reload', function (): void {
    $this->actingAs($this->user);

    expect(Livewire::test(Revenue::class)->effects['xjs'] ?? [])->toBeEmpty();
});
