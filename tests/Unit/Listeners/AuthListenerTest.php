<?php

use FluxErp\Listeners\Auth\LoginListener;
use FluxErp\Listeners\Auth\LogoutListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Spatie\Activitylog\Models\Activity;

test('login listener logs activity', function (): void {
    $event = new Login('web', $this->user, false);

    (new LoginListener())->handle($event);

    $activity = Activity::query()->where('event', 'logged_in')->latest()->first();

    expect($activity)
        ->not->toBeNull()
        ->causer_id->toBe($this->user->getKey());
});

test('logout listener logs activity', function (): void {
    $event = new Logout('web', $this->user);

    (new LogoutListener())->handle($event);

    $activity = Activity::query()->where('event', 'logged_out')->latest()->first();

    expect($activity)
        ->not->toBeNull()
        ->causer_id->toBe($this->user->getKey());
});

test('login and logout activity descriptions are translated', function (string $locale, string $login, string $logout): void {
    app()->setLocale($locale);

    (new LoginListener())->handle(new Login('web', $this->user, false));
    (new LogoutListener())->handle(new Logout('web', $this->user));

    expect(Activity::query()->where('event', 'logged_in')->latest('id')->value('description'))
        ->toBe($this->user->name . ' ' . $login)
        ->and(Activity::query()->where('event', 'logged_out')->latest('id')->value('description'))
        ->toBe($this->user->name . ' ' . $logout);
})
    ->with([
        ['en', 'logged in', 'logged out'],
        ['de', 'hat sich angemeldet', 'hat sich abgemeldet'],
    ]);
