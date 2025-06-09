<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Token;

class TokenList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'description',
    ];

    protected string $model = Token::class;
}
