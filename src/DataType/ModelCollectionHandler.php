<?php

namespace FluxErp\DataType;

use Illuminate\Database\Eloquent\Collection;

/**
 * Handle serialization of Eloquent collections.
 */
class ModelCollectionHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'collection';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Collection;
    }

    public function serializeValue(mixed $value): string
    {
        $items = [];
        foreach ($value as $key => $model) {
            $items[$key] = [
                'class' => get_class($model),
                'key' => $model->exists ? $model->getKey() : null,
            ];
        }

        return json_encode(['class' => get_class($value), 'items' => $items]);
    }

    /**
     * @return mixed|null
     */
    public function unserializeValue(?string $serializedValue): mixed
    {
        if (is_null($serializedValue)) {
            return null;
        }

        $data = json_decode($serializedValue, true);

        $collection = new $data['class']();
        $models = $this->loadModels($data['items']);

        // Repopulate collection keys with loaded models.
        foreach ($data['items'] as $key => $item) {
            if (is_null($item['key'])) {
                $collection[$key] = new $item['class']();
            } elseif (isset($models[$item['class']][$item['key']])) {
                $collection[$key] = $models[$item['class']][$item['key']];
            }
        }

        return $collection;
    }

    /**
     * Load each model instance, grouped by class.
     */
    private function loadModels(array $items): array
    {
        $classes = [];
        $results = [];

        // Retrieve a list of keys to load from each class.
        foreach ($items as $item) {
            if (! is_null($item['key'])) {
                $classes[$item['class']][] = $item['key'];
            }
        }

        // Iterate list of classes and load all records matching a key.
        foreach ($classes as $class => $keys) {
            $model = new $class();
            $results[$class] = $model->whereIn($model->getKeyName(), $keys)->get()->keyBy($model->getKeyName());
        }

        return $results;
    }
}
