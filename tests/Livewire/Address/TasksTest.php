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

test('refuses a reminder with invalid minutes before', function (string $field, mixed $minutes): void {
    $address = Address::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);
    $type = str_starts_with($field, 'start') ? 'start' : 'due';

    Livewire::test(Tasks::class, ['modelId' => $address->getKey()])
        ->set('task.name', $name = Str::uuid()->toString())
        ->set('task.' . $type . '_date', now()->addDay()->toDateString())
        ->set('task.has_' . $type . '_reminder', true)
        ->set('task.' . $field, $minutes)
        ->call('save')
        ->assertHasErrors('task.' . $field)
        ->assertReturned(false);

    $this->assertDatabaseMissing('tasks', ['name' => $name]);
})->with([
    'missing start minutes' => ['start_reminder_minutes_before', null],
    'negative start minutes' => ['start_reminder_minutes_before', -5],
    'missing due minutes' => ['due_reminder_minutes_before', null],
    'negative due minutes' => ['due_reminder_minutes_before', -5],
]);

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
