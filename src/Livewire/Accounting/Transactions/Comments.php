<?php

namespace FluxErp\Livewire\Accounting\Transactions;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Transaction;

class Comments extends BaseComments
{
    protected string $modelType = Transaction::class;
}
