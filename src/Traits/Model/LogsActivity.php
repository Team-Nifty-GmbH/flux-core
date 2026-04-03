<?php

namespace FluxErp\Traits\Model;

use Spatie\Activitylog\Models\Concerns\LogsActivity as BaseLogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\ModelStates\State;

trait LogsActivity
{
    use BaseLogsActivity {
        BaseLogsActivity::buildChanges as baseBuildChanges;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('model_events')
            ->logAll()
            ->dontLogIfAttributesChangedOnly(['created_at', 'updated_at'])
            ->dontLogEmptyChanges()
            ->logExcept(['created_at', 'updated_at', 'deleted_at'])
            ->logOnlyDirty();
    }

    protected function buildChanges(string $processingEvent): array
    {
        $changes = $this->baseBuildChanges($processingEvent);

        if (! array_key_exists('old', $changes)) {
            return $changes;
        }

        $changed = array_keys(
            array_intersect_key(
                data_get($changes, 'attributes') ?? [],
                data_get($changes, 'old') ?? []
            )
        );

        foreach ($changed as $key) {
            $attribute = data_get($changes, 'attributes.' . $key);
            $old = data_get($changes, 'old.' . $key);

            if (
                $old instanceof State
                && $attribute instanceof State
                && $old::class === $attribute::class
            ) {
                data_forget($changes, 'old.' . $key);
                data_forget($changes, 'attributes.' . $key);
            }
        }

        return $changes;
    }
}
