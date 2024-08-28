<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\ProductPropertyGroupList as BaseProductPropertyGroupList;
use Illuminate\View\ComponentAttributeBag;

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
            '%'.$this->search.'%',
        ]];

        parent::startSearch();
    }

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                record.id === productPropertyGroup?.id && 'bg-primary-100 dark:bg-primary-800'
            JS,
        ]);
    }

    protected function allowSoftDeletes(): bool
    {
        return false;
    }
}
