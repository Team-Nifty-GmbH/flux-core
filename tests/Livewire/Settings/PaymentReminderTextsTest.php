<?php

use FluxErp\Livewire\Settings\PaymentReminderTexts;
use FluxErp\Models\PaymentReminderText;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentReminderTexts::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(PaymentReminderTexts::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('paymentReminderTextForm.id', null)
        ->assertSet('paymentReminderTextForm.reminder_subject', null)
        ->assertSet('paymentReminderTextForm.reminder_body', null)
        ->assertSet('paymentReminderTextForm.reminder_level', null);
});

test('edit with model fills form', function (): void {
    $text = PaymentReminderText::factory()->create();

    Livewire::test(PaymentReminderTexts::class)
        ->call('edit', $text->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('paymentReminderTextForm.id', $text->getKey())
        ->assertSet('paymentReminderTextForm.reminder_subject', $text->reminder_subject)
        ->assertSet('paymentReminderTextForm.reminder_level', $text->reminder_level);
});

test('can create payment reminder text', function (): void {
    Livewire::test(PaymentReminderTexts::class)
        ->call('edit')
        ->set('paymentReminderTextForm.reminder_subject', 'Test Reminder Subject')
        ->set('paymentReminderTextForm.reminder_body', 'Test reminder body content')
        ->set('paymentReminderTextForm.reminder_level', 99)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('payment_reminder_texts', [
        'reminder_subject' => 'Test Reminder Subject',
        'reminder_level' => 99,
    ]);
});

test('can update payment reminder text', function (): void {
    $text = PaymentReminderText::factory()->create();

    Livewire::test(PaymentReminderTexts::class)
        ->call('edit', $text->getKey())
        ->assertSet('paymentReminderTextForm.id', $text->getKey())
        ->set('paymentReminderTextForm.reminder_subject', 'Updated Subject')
        ->set('paymentReminderTextForm.reminder_body', 'Updated body content')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $refreshed = $text->refresh();
    expect($refreshed->reminder_subject)->toEqual('Updated Subject');
    expect($refreshed->reminder_body)->toEqual('Updated body content');
});

test('can delete payment reminder text', function (): void {
    $text = PaymentReminderText::factory()->create();
    $textId = $text->getKey();

    Livewire::test(PaymentReminderTexts::class)
        ->call('delete', $textId)
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('payment_reminder_texts', [
        'id' => $textId,
    ]);
});

test('edit resets form when switching from existing to new', function (): void {
    $text = PaymentReminderText::factory()->create();

    Livewire::test(PaymentReminderTexts::class)
        ->call('edit', $text->getKey())
        ->assertSet('paymentReminderTextForm.id', $text->getKey())
        ->assertSet('paymentReminderTextForm.reminder_subject', $text->reminder_subject)
        ->call('edit')
        ->assertSet('paymentReminderTextForm.id', null)
        ->assertSet('paymentReminderTextForm.reminder_subject', null);
});
