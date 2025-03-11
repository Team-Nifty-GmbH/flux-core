<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Tag;

class TagList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'type',
    ];

    protected string $model = Tag::class;
}
