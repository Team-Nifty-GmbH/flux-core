<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class MorphExists implements DataAwareRule, ValidationRule
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

        $morphClass = morphed_model($model);
        if (! $morphClass && (! class_exists($model) || ! app($model) instanceof Model)) {
            $fail(sprintf('%s is not a valid model.', $model))->translate();

            return;
        }

        $model = $morphClass ?: $model;

        if (! resolve_static($model, 'query')->whereKey($value)->exists()) {
            $fail(sprintf('Record with id %s doesnt exist in %s.', $value, $model))->translate();
        }
    }

    public function setData($data): static
    {
        $this->data = $data;

        return $this;
    }
}
