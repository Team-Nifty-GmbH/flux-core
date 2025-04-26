<?php

namespace FluxErp\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as BaseLogsActivity;
use Spatie\ModelStates\State;

trait LogsActivity
{
    use BaseLogsActivity {
        BaseLogsActivity::attributeValuesToBeLogged as baseAttributeValuesToBeLogged;
    }

    public function attributeValuesToBeLogged(string $processingEvent): array
    {
        $properties = $this->baseAttributeValuesToBeLogged($processingEvent);

        if (! array_key_exists('old', $properties)) {
            return $properties;
        }

        $changed = array_keys(
            array_intersect_key(
                data_get($properties, 'attributes'),
                data_get($properties, 'old')
            )
        );

        foreach ($changed as $key) {
            $attribute = data_get($properties, 'attributes.' . $key);
            $old = data_get($properties, 'old.' . $key);

            if ($old instanceof State && $attribute instanceof State && $old::class === $attribute::class) {
                data_forget($properties, 'old.' . $key);
                data_forget($properties, 'attributes.' . $key);
            }
        }

        return $properties;
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('model_events')
            ->logAll()
            ->dontLogIfAttributesChangedOnly(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->logExcept(['created_at', 'updated_at', 'deleted_at'])
            ->logOnlyDirty();
    }
}
