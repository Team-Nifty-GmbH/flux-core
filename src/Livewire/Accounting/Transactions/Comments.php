<?php

namespace FluxErp\Livewire\Accounting\Transactions;

use FluxErp\Models\Transaction;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = Transaction::class;
}
