<?php

use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Support\TallstackUI\Interactions\Toast;

test('a toast without an explicit timeout closes after the configured timeout', function (): void {
    $toast = new class() extends Toast
    {
        public function payload(): array
        {
            return $this->additional();
        }
    };

    expect($toast->payload()['timeout'])->toBe(config('ts-ui.components.toast.1.timeout'));
});

test('a toast notification without an explicit timeout closes after the configured timeout', function (): void {
    expect(ToastNotification::make()->toArray()['timeout'])->toBe(config('ts-ui.components.toast.1.timeout'));
});

test('an explicit toast timeout wins over the configured one', function (): void {
    expect(ToastNotification::make()->timeout(10)->toArray()['timeout'])->toBe(10);
});

test('a toast notification mail keeps the description markup but drops active content', function (): void {
    $mail = ToastNotification::make()
        ->notifiable($this->user)
        ->title('Task')
        ->description('<p>Task <strong>Neu</strong><img src="x" onerror="alert(1)"></p>')
        ->toMail();

    expect((string) data_get($mail->introLines, 0))->toBe('<p>Task <strong>Neu</strong><img src="x" /></p>');
});
