<?php

namespace FluxErp\Rules;

use FluxErp\Traits\Makeable;

class ValidStateRule extends \Spatie\ModelStates\Validation\ValidStateRule
{
    use Makeable;

    public function __construct(string $abstractStateClass)
    {
        $baseStateClass = resolve_static($abstractStateClass, 'class');
        parent::__construct($baseStateClass);
    }
}
