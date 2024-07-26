<?php

namespace FluxErp\DataType;

use Illuminate\Database\Eloquent\Model;

/**
 * Handle serialization of Eloquent Models.
 */
class ModelHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'model';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Model;
    }

    public function serializeValue(mixed $value): string
    {
        if ($value->exists) {
            return get_class($value) . '#' . $value->getKey();
        }

        return get_class($value);
    }

    /**
     * @return mixed
     */
    public function unserializeValue(?string $serializedValue)
    {
        if (is_null($serializedValue)) {
            return null;
        }

        // Return blank instances.
        if (! str_contains($serializedValue, '#')) {
            return app($serializedValue);
        }

        // Fetch specific instances.
        [$class, $id] = explode('#', $serializedValue);

        return app($class)->findOrFail($id);
    }
}
