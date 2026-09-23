<?php

namespace FluxErp\Tests\Fixtures\Policies;

use FluxErp\Models\MailAccount;
use FluxErp\Models\User;

class OverriddenMailAccountPolicy
{
    public function view(User $user, MailAccount $mailAccount): bool
    {
        return true;
    }
}
