<?php

namespace FluxErp\Relations;

use FluxErp\Traits\HasAdditionalColumns;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BelongsToManyObservable extends BelongsToMany
{
    public function sync($ids, $detaching = true): array
    {
        $this->parent->fireModelEvent('pivotSyncing', true);

        $sync = parent::sync($ids, $detaching);

        $operations = ['attached', 'detached', 'updated'];
        $doneOperations = array_diff($operations, array_keys($sync, []));

        if (array_key_exists(HasAdditionalColumns::class, class_uses($this->parent))) {
            $createdAt = Carbon::parse($this->parent->created_at)->addSecond();
            if ($createdAt->gte(Carbon::now())) {
                $this->parent->fireModelEvent('pivotSyncCreated', true);
            } elseif (count($doneOperations) !== 0) {
                $this->parent->fireModelEvent('pivotSyncUpdated', true);
            }
        }

        return $sync;
    }
}
