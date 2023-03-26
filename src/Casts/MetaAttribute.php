<?php

namespace FluxErp\Casts;

use FluxErp\Traits\HasAdditionalColumns;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MetaAttribute implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function get($model, string $key, mixed $value, array $attributes)
    {
        if (in_array(HasAdditionalColumns::class, class_uses($model))) {
            return $model->getMeta($key, $value ?? $model->getFallbackValue($key));
        }

        return $value;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function set($model, string $key, mixed $value, array $attributes)
    {
        return $value;
    }
}
