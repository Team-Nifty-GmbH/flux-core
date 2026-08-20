<?php

namespace FluxErp\Tests\Fixtures\Standalone\Policies;

use FluxErp\Models\User;

class StandaloneRecordPolicy
{
    public function view(User $user): bool
    {
        return true;
    }
}
