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

    protected ?string $column;

    protected Model $model;

    public function __construct(Model|string $model, ?string $column = null)
    {
        if (is_string($model)) {
            $model = app($model);
        }

        $this->model = $model;
        $this->column = $column;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $attribute = $this->column ?? Str::afterLast($attribute, '.');

        try {
            $this->model->query()->where($attribute, $value)->sole($attribute);
        } catch (MultipleRecordsFoundException) {
            $fail('The selected :attribute has multiple records.')->tranlsate();
        }
    }
}
