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

test('a visible toast keeps its progress when navigating', function (): void {
    $page = visit(route('dashboard'))->assertNoSmoke();

    $page->script(<<<'JS'
        () => new Promise((resolve) => {
            const wait = () => document.querySelectorAll('[x-data^="tallstackui_toastBase"]').length === 2
                ? resolve()
                : setTimeout(wait, 200);
            wait();
        })
    JS);

    $page->script("() => window.\$tsui.interaction('toast').success('Kept while navigating').timeout(30).send()");

    $page->wait(2);

    $page->script(<<<'JS'
        () => {
            document.addEventListener('livewire:navigated', () => window.navigatedForTest = true, { once: true });
            Livewire.navigate('/contacts/contacts');
        }
    JS);

    waitForCondition($page, '() => window.navigatedForTest === true', 10000);

    $progress = $page->script(<<<'JS'
        () => [...document.querySelectorAll('[x-data^="tallstackui_toastLoop"]')]
            .find((toast) => toast.innerText.includes('Kept while navigating'))
            ?.querySelector('[x-ref="progress"]')
            ?.getAnimations()[0]
            ?.currentTime ?? null
    JS);

    expect($progress)->toBeGreaterThan(1500)
        ->and(visibleToastsContaining($page, 'Kept while navigating'))->toBe(1);

    $page->assertNoJavascriptErrors();
});

test('closing one notification in the slide keeps the other unread notifications listed', function (): void {
    foreach (['First unread', 'Second unread', 'Third unread'] as $description) {
        $this->user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => [
                'title' => 'Unread notification',
                'description' => $description,
            ],
        ]);
    }

    $page = visit(route('dashboard'))->assertNoSmoke();

    waitForElement($page, '[wire\\\\:click="showNotifications()"]', 10000);

    $page->script(<<<'JS'
        () => document.querySelector('[wire\\:click="showNotifications()"]').click()
    JS);

    waitForCondition($page, <<<'JS'
        () => [...document.querySelectorAll('[x-ref="toasts"] [x-data^="tallstackui_toastLoop"]')]
            .filter((toast) => toast.offsetParent !== null).length === 3
    JS, 10000);

    $page->script(<<<'JS'
        () => [...document.querySelectorAll('[x-ref="toasts"] [x-data^="tallstackui_toastLoop"]')]
            .find((toast) => toast.innerText.includes('First unread'))
            .querySelector('button')
            .click()
    JS);

    $page->wait(2);

    $listed = $page->script(<<<'JS'
        () => [...document.querySelectorAll('[x-ref="toasts"] [x-data^="tallstackui_toastLoop"]')]
            .filter((toast) => toast.offsetParent !== null)
            .map((toast) => toast.innerText.includes('Second unread')
                ? 'second'
                : (toast.innerText.includes('Third unread') ? 'third' : 'other'))
            .sort()
    JS);

    expect($listed)->toBe(['second', 'third'])
        ->and($this->user->unreadNotifications()->count())->toBe(2);

    $page->assertNoJavascriptErrors();
});
