<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class MorphClassExists extends ClassExists
{
    public function __construct(array|string $uses = [], ?string $implements = null)
    {
        parent::__construct($uses, Model::class, $implements);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $morphClass = Relation::getMorphedModel($value)) {
            $fail(sprintf('%s is not a valid morph class.', $value))->translate();
        }

        parent::validate($attribute, $morphClass, $fail);
    }
}
