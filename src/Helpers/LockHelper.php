<?php

namespace FluxErp\Helpers;

use FluxErp\Actions\Lock\ForceUnlock;
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
            ForceUnlock::make(['model_type' => $record[1], 'model_id' => $record[2]])->execute();
        }
    }

    public function get()
    {
        return $this->locks;
    }
}
