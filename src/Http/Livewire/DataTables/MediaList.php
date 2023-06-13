<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Media;
use TeamNiftyGmbH\DataTable\DataTable;

class MediaList extends DataTable
{
    protected string $model = Media::class;

    public array $enabledCols = [
        'file_name',
        'collection_name',
    ];

    public function itemToArray($item): array
    {
        $item->makeVisible('collection_name');

        return parent::itemToArray($item);
    }
}
