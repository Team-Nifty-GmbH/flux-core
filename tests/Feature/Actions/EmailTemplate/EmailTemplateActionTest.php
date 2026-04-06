<?php

use FluxErp\Actions\EmailTemplate\CreateEmailTemplate;
use FluxErp\Actions\EmailTemplate\DeleteEmailTemplate;
use FluxErp\Actions\EmailTemplate\UpdateEmailTemplate;
use FluxErp\Models\EmailTemplate;

test('create email template', function (): void {
    $template = CreateEmailTemplate::make(['name' => 'Order Confirmation'])
        ->validate()->execute();

    expect($template)->toBeInstanceOf(EmailTemplate::class)
        ->name->toBe('Order Confirmation');
});

test('create email template requires name', function (): void {
    CreateEmailTemplate::assertValidationErrors([], 'name');
});

test('update email template', function (): void {
    $template = EmailTemplate::factory()->create();

    $updated = UpdateEmailTemplate::make([
        'id' => $template->getKey(),
        'name' => 'Shipping Notification',
    ])->validate()->execute();

    expect($updated->name)->toBe('Shipping Notification');
});

test('delete email template', function (): void {
    $template = EmailTemplate::factory()->create();

    expect(DeleteEmailTemplate::make(['id' => $template->getKey()])
        ->validate()->execute())->toBeTrue();
});
