<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Eloquent\Model;

class MorphExists implements InvokableRule, DataAwareRule
{
    protected array $data;

    private string $modelAttribute;

    public function __construct(string $modelAttribute = 'model_type')
    {
        $this->modelAttribute = $modelAttribute;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
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
