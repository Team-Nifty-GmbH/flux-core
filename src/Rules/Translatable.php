<?php

namespace FluxErp\Rules;

use Closure;
use FluxErp\Traits\HasAttributeTranslations;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class Translatable implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function __construct(
        protected string $modelAttribute = 'model_type',
        protected ?string $modelClass = null
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_null($this->modelClass)) {
            $model = resolve_static($this->modelClass, 'class');
        } else {
            $model = morphed_model(data_get($this->data, $this->modelAttribute));
        }

        if (! $model) {
            $fail('No model class given')->translate();
        }

        if (! in_array(HasAttributeTranslations::class, class_uses_recursive($model))) {
            $fail(sprintf('%s has no attribute translations', $model))->translate();
        }

        if (! in_array($value, $model::getTranslatableAttributes())) {
            $fail(sprintf('%s is not translatable', $value))->translate();
        }
    }
}
