<?php

use FluxErp\Livewire\Settings\Profile;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Profile::class)
        ->assertOk();
});

test('every subscribable notification has a german label on the profile', function (): void {
    app()->setLocale('en');
    $englishLabels = Livewire::test(Profile::class)->get('notifications');

    app()->setLocale('de');
    $germanLabels = Livewire::test(Profile::class)->get('notifications');

    expect($germanLabels)->not->toBeEmpty();

    foreach ($germanLabels as $notificationClass => $germanLabel) {
        expect($germanLabel)
            ->not->toBe(data_get($englishLabels, $notificationClass), $notificationClass . ' has no german label');
    }
});
