<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Tag;

class TagList extends BaseDataTable
{
    protected string $model = Tag::class;

    public array $enabledCols = [
        'name',
        'type',
    ];
}
