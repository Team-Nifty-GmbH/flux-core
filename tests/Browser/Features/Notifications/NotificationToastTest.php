<?php

use Illuminate\Support\Str;

function sendNotify(mixed $page, array $notify): void
{
    $payload = json_encode($notify);

    $page->script(<<<JS
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Notifications component not loaded')), 10000);
            const send = () => {
                const component = Livewire.all().find((c) => c.name === 'features.notifications');

                if (! component) {
                    setTimeout(send, 200);

                    return;
                }

                clearTimeout(timeout);
                component.\$wire.sendNotify({$payload}).then(resolve);
            };
            send();
        })
    JS);
}

function visibleToastsContaining(mixed $page, string $text): int
{
    return $page->script(<<<JS
        () => [...document.querySelectorAll('[x-data^="tallstackui_toastLoop"]')]
            .filter((toast) => toast.offsetParent !== null && toast.innerText.includes('{$text}'))
            .length
    JS);
}

test('a notification with a toast id shows a toast', function (): void {
    $page = visit(route('dashboard'))->assertNoSmoke();

    sendNotify($page, [
        'title' => 'Import finished',
        'description' => 'First state',
        'toastId' => 'job-1',
        'contextId' => 'job-1',
    ]);

    $page->wait(1);

    expect(visibleToastsContaining($page, 'First state'))->toBe(1);

    $page->assertNoJavascriptErrors();
});

test('a second notification with the same toast id updates the toast', function (): void {
    $page = visit(route('dashboard'))->assertNoSmoke();

    sendNotify($page, [
        'title' => 'Import running',
        'description' => 'First state',
        'toastId' => 'job-2',
        'contextId' => 'job-2',
        'persistent' => true,
    ]);

    $page->wait(1);

    sendNotify($page, [
        'title' => 'Import running',
        'description' => 'Second state',
        'toastId' => 'job-2',
        'contextId' => 'job-2',
        'persistent' => true,
    ]);

    $page->wait(1);

    expect(visibleToastsContaining($page, 'Import running'))->toBe(1)
        ->and(visibleToastsContaining($page, 'Second state'))->toBe(1);

    $page->assertNoJavascriptErrors();
});

test('unread notifications are listed in the notification slide', function (): void {
    $this->user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'test',
        'data' => [
            'title' => 'Listed notification',
            'description' => 'Shown in the slide',
        ],
    ]);

    $page = visit(route('dashboard'))->assertNoSmoke();

    waitForElement($page, '[wire\\\\:click="showNotifications()"]', 10000);

    $page->script(<<<'JS'
        () => document.querySelector('[wire\\:click="showNotifications()"]').click()
    JS);

    $page->wait(1.5);

    $inSlide = $page->script(<<<'JS'
        () => {
            const list = document.querySelector('[x-ref="toasts"]');

            return [...list.querySelectorAll('[x-data^="tallstackui_toastLoop"]')]
                .filter((toast) => toast.offsetParent !== null && toast.innerText.includes('Shown in the slide'))
                .length;
        }
    JS);

    expect($inSlide)->toBe(1);

    $page->assertNoJavascriptErrors();
});

test('a regular toast is shown once and not copied into the notification list', function (): void {
    $page = visit(route('dashboard'))->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const wait = () => document.querySelectorAll('[x-data^="tallstackui_toastBase"]').length === 2
                ? resolve()
                : setTimeout(wait, 200);
            wait();
        })
    JS);

    $page->script("() => window.\$tsui.interaction('toast').success('Saved successfully').send()");

    $page->wait(1);

    expect(visibleToastsContaining($page, 'Saved successfully'))->toBe(1);

    $page->assertNoJavascriptErrors();
});
