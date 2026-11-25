<?php

use FluxErp\Livewire\Settings\Notifications;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Notifications::class)
        ->assertOk();
});

test('every subscribable notification has a german label', function (): void {
    $component = Livewire::test(Notifications::class);

    expect($component->get('notifications'))->not->toBeEmpty();

    foreach ($component->get('notifications') as $notificationClass) {
        app()->setLocale('en');
        $englishLabel = $component->instance()->translate($notificationClass);

        app()->setLocale('de');
        $germanLabel = $component->instance()->translate($notificationClass);

        expect($germanLabel)
            ->not->toBe($englishLabel, $notificationClass . ' has no german label');
    }
});
