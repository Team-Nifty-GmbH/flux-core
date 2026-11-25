<?php

use FluxErp\Livewire\EditMail;
use FluxErp\Models\Contact;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Order;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(EditMail::class)
        ->assertOk();
});

test('batch send merge logic preserves individual communicatables and attachments', function (): void {
    $orderIds = [101, 102, 103];

    $mailMessages = [];
    foreach ($orderIds as $orderId) {
        $mailMessages[] = [
            'to' => ['test-' . $orderId . '@example.com'],
            'subject' => 'Order ' . $orderId,
            'html_body' => '<p>Test</p>',
            'communicatables' => [
                [
                    'communicatable_type' => 'order',
                    'communicatable_id' => $orderId,
                ],
            ],
            'attachments' => [
                ['name' => 'doc-' . $orderId . '.pdf'],
            ],
        ];
    }

    $editedMailMessage = $mailMessages[0];
    $editedMailMessage['subject'] = 'Edited Subject';

    $mergedData = [];
    foreach ($mailMessages as $mailMessage) {
        $data = $mailMessage;

        $editedWithoutTo = $editedMailMessage;
        unset($editedWithoutTo['to'], $editedWithoutTo['communicatables'], $editedWithoutTo['attachments']);
        $data = array_merge($data, array_filter($editedWithoutTo));

        $mergedData[] = $data;
    }

    foreach ($orderIds as $index => $orderId) {
        expect($mergedData[$index]['communicatables'][0]['communicatable_id'])->toBe($orderId);
        expect($mergedData[$index]['to'])->toBe(['test-' . $orderId . '@example.com']);
        expect($mergedData[$index]['attachments'][0]['name'])->toBe('doc-' . $orderId . '.pdf');
        expect($mergedData[$index]['subject'])->toBe('Edited Subject');
    }
});

test('create from session fills mail message from session data', function (): void {
    $key = 'mail_' . str()->uuid()->toString();
    session()->put($key, [
        [
            'to' => ['customer@example.com'],
            'subject' => 'Cancellation Confirmation',
            'html_body' => '<p>Test</p>',
        ],
    ]);

    Livewire::actingAs($this->user)
        ->test(EditMail::class)
        ->call('createFromSession', $key)
        ->assertOk()
        ->assertSet('mailMessage.subject', 'Cancellation Confirmation');

    expect(session()->has($key))->toBeFalse();
});

test('create from session with already consumed key fails gracefully', function (): void {
    Livewire::actingAs($this->user)
        ->test(EditMail::class)
        ->call('createFromSession', 'mail_' . str()->uuid()->toString())
        ->assertOk();
});

test('applies only templates offered for the model type', function (): void {
    $otherTemplate = EmailTemplate::factory()->create([
        'subject' => 'Other subject',
        'model_type' => morph_alias(Order::class),
    ]);
    $template = EmailTemplate::factory()->create([
        'subject' => 'Offered subject',
        'model_type' => morph_alias(Contact::class),
    ]);

    $component = Livewire::test(EditMail::class)
        ->call('create', [], morph_alias(Contact::class));

    $component->set('selectedTemplateId', $otherTemplate->getKey())
        ->call('applyTemplate')
        ->assertSet('mailMessage.subject', null);

    $component->set('selectedTemplateId', $template->getKey())
        ->call('applyTemplate')
        ->assertSet('mailMessage.subject', 'Offered subject');
});

test('does not accept template settings from the client', function (string $property, array|string $value): void {
    Livewire::test(EditMail::class)
        ->set($property, $value);
})
    ->with([
        ['emailTemplates', []],
        ['templateData', []],
        ['templateModelType', 'contact'],
    ])
    ->throws(CannotUpdateLockedPropertyException::class);
