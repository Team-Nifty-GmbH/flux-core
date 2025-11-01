<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Employee;

class Comments extends BaseComments
{
    protected string $modelType = Employee::class;
}
