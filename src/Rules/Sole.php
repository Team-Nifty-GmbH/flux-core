<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Str;

class Sole implements ValidationRule
{
    public bool $implicit = false;

    public Model $model;

    public function __construct(Model|string $model)
    {
        if (is_string($model)) {
            $model = app($model);
        }

        $this->model = $model;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $attribute = Str::afterLast($attribute, '.');

        try {
            $this->model->query()->where($attribute, $value)->sole($attribute);
        } catch (MultipleRecordsFoundException) {
            $fail('The selected :attribute has multiple records.')->tranlsate();
        }
    }
}
