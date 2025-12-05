<?php

namespace FluxErp\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyObservable extends BelongsToMany
{
    public function sync($ids, $detaching = true): array
    {
        $this->parent->fireModelEvent('pivotSyncing', true);

        return parent::sync($ids, $detaching);
    }
}
