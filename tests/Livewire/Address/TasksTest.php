<?php

use FluxErp\Livewire\Address\Tasks;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'can_login' => false,
        'is_active' => true,
    ]);

    Livewire::test(Tasks::class, ['modelId' => $address->id])
        ->assertOk();
});

test('renders the reminder fields', function (): void {
    $address = Address::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);

    Livewire::test(Tasks::class, ['modelId' => $address->getKey()])
        ->assertSeeHtml('x-show="$wire.task.start_date"')
        ->assertSeeHtml('wire:model="task.has_start_reminder"')
        ->assertSeeHtml('wire:model="task.start_reminder_minutes_before"')
        ->assertSeeHtml('x-show="$wire.task.due_date"')
        ->assertSeeHtml('wire:model="task.has_due_reminder"')
        ->assertSeeHtml('wire:model="task.due_reminder_minutes_before"');
});

test('can create a task with reminders on the address', function (): void {
    $address = Address::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);

    Livewire::test(Tasks::class, ['modelId' => $address->getKey()])
        ->set('task.name', $name = Str::uuid()->toString())
        ->set('task.start_date', now()->addDay()->toDateString())
        ->set('task.has_start_reminder', true)
        ->set('task.start_reminder_minutes_before', 15)
        ->set('task.due_date', now()->addDays(2)->toDateString())
        ->set('task.has_due_reminder', true)
        ->set('task.due_reminder_minutes_before', 30)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $name,
        'model_type' => morph_alias(Address::class),
        'model_id' => $address->getKey(),
        'has_start_reminder' => true,
        'start_reminder_minutes_before' => 15,
        'has_due_reminder' => true,
        'due_reminder_minutes_before' => 30,
    ]);
});
