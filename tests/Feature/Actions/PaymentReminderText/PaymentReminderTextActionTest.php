<?php

use FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\UpdatePaymentReminderText;
use FluxErp\Models\PaymentReminderText;

test('create payment reminder text', function (): void {
    $text = CreatePaymentReminderText::make([
        'reminder_level' => 1,
        'reminder_subject' => 'Payment Overdue',
        'reminder_body' => 'Please pay your invoice.',
    ])->validate()->execute();

    expect($text)->toBeInstanceOf(PaymentReminderText::class);
});

test('update payment reminder text', function (): void {
    $text = PaymentReminderText::factory()->create();

    $updated = UpdatePaymentReminderText::make([
        'id' => $text->getKey(),
        'reminder_level' => $text->reminder_level,
        'reminder_subject' => 'Updated Subject',
    ])->validate()->execute();

    expect($updated->reminder_subject)->toBe('Updated Subject');
});

test('delete payment reminder text', function (): void {
    $text = PaymentReminderText::factory()->create();

    expect(DeletePaymentReminderText::make(['id' => $text->getKey()])
        ->validate()->execute())->toBeTrue();
});
