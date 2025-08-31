<?php

namespace FluxErp\Livewire\EmployeeDay;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\EmployeeDay;

class Comments extends BaseComments
{
    protected string $modelType = EmployeeDay::class;
}
