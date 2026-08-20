<?php

namespace FluxErp\Support\Collection;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class UserCollection extends Collection
{
    public function groupByFirstname(): BaseCollection
    {
        return $this->groupBy(fn (User $user) => strtolower((string) $user->firstname));
    }

    public function keyByUserCode(): static
    {
        return $this->keyBy(fn (User $user) => strtolower((string) $user->user_code));
    }
}
