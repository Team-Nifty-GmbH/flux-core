<?php

namespace FluxErp\Rules;

use Closure;

class ModelDoesntExist extends ModelExists
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = $this->clone();
        if ($query->where($this->key, $value)->exists()) {
            $fail('validation.model_doesnt_exist')->translate();
        }
    }
}
