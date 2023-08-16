<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class MorphExists implements ValidationRule, DataAwareRule
{
    protected array $data;

    private string $modelAttribute;

    public function __construct(string $modelAttribute = 'model_type')
    {
        $this->modelAttribute = $modelAttribute;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $prefix = strpos($attribute, '.') ? pathinfo($attribute, PATHINFO_FILENAME) . '.' : null;

        $model = data_get($this->data, $prefix . $this->modelAttribute);

        if (! $model) {
            $fail(sprintf('%s is not defined.', $this->modelAttribute))->translate();

            return;
        }

        if (! class_exists($model) || ! new $model() instanceof Model) {
            $fail(sprintf('%s is not a valid model.', $model))->translate();

            return;
        }

        if (! $model::query()->where((new $model())->getKeyName(), $value)->exists()) {
            $fail(sprintf('Record with id %s doesnt exist in %s.', $value, $model))->translate();
        }
    }

    /**
     * @return MediaUploadType|$this
     */
    public function setData($data): MediaUploadType|static
    {
        $this->data = $data;

        return $this;
    }
}
