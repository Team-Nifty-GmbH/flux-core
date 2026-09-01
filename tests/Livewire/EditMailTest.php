<?php

use FluxErp\Livewire\EditMail;
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
