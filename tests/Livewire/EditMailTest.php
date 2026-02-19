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
