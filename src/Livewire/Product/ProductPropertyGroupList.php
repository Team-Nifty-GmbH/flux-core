<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\ProductPropertyGroupList as BaseProductPropertyGroupList;

class ProductPropertyGroupList extends BaseProductPropertyGroupList
{
    public bool $showFilterInputs = false;

    public ?bool $isSearchable = true;

    public bool $hasSidebar = false;

    public function startSearch(): void
    {
        $this->filters = [[
            'name',
            'like',
            '%' . $this->search . '%',
        ]];

        parent::startSearch();
    }
}
