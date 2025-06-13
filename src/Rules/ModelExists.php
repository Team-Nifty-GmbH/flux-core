<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelExists extends Builder implements ValidationRule
{
    public bool $implicit = false;

    protected string $key;

    public function __construct(Model|string $model, ?string $key = null)
    {
        if (is_string($model)) {
            $model = app($model);
        }

        $this->key = $key ?: $model->getQualifiedKeyName();

        parent::__construct($model::query()->getQuery());

        $this->setModel($model);
        foreach ($model->getGlobalScopes() as $identifier => $scope) {
            $this->withGlobalScope($identifier, $scope);
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = $this->clone();
        if ($query->where($this->key, $value)->doesntExist()) {
            $fail('validation.model_exists')->translate();
        }
    }
}
