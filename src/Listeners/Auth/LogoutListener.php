<?php

namespace FluxErp\Listeners\Auth;

class LogoutListener
{
    public function handle(object $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->event('logged_out')
            ->log(trim(($event->user->name ?? '') . ' logged out'));
    }
}
