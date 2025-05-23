<?php

namespace FluxErp\Listeners\Auth;

class LoginListener
{
    public function handle(object $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard,
            ])
            ->event('logged_in')
            ->log(trim(($event->user->name ?? '') . ' logged in'));
    }
}
