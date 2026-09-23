<?php

namespace FluxErp\Policies;

use FluxErp\Models\MailAccount;
use FluxErp\Models\User;

/**
 * Deliberately defines only the view ability: undefined abilities deny by
 * Laravel's default; further abilities are added together with their consumers.
 */
class MailAccountPolicy
{
    public function view(User $user, MailAccount $mailAccount): bool
    {
        return $mailAccount->relationLoaded('users')
            ? $mailAccount->users->contains($user)
            : $mailAccount->users()->whereKey($user->getKey())->exists();
    }
}
