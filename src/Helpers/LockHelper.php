<?php

namespace FluxErp\Helpers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

class LockHelper
{
    private array $locks;

    public function __construct(Authenticatable $user)
    {
        $this->locks = Cache::get('user-locks:' . $user->getMorphClass() . ':' . $user->id, []);
    }

    public function delete(): void
    {
        foreach ($this->locks as $lock => $owner) {
            $record = explode(':', $lock);
            $model = $record[1]::query()
                ->whereKey($record[2])
                ->firstOrFail();

            $model->forceUnlock();
        }
    }

    public function get()
    {
        return $this->locks;
    }
}
