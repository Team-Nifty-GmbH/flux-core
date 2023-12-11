<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductOptionGroup;
use Illuminate\View\ComponentAttributeBag;
use TeamNiftyGmbH\DataTable\DataTable;

class ProductOptionGroupList extends DataTable
{
    protected string $model = ProductOptionGroup::class;

    public bool $showFilterInputs = false;

    public ?bool $isSearchable = true;

    public bool $hasSidebar = false;

    public array $enabledCols = [
        'name',
        'selected',
    ];

    public function updatedSearch(): void
    {
        $this->filters = [[
            'name',
            'like',
            '%' . $this->search . '%',
        ]];

        parent::updatedSearch();
    }

    public function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                record.id === productOptionGroup?.id && 'bg-primary-100'
            JS,
        ]);
    }


    public function getCellAttributes(): ComponentAttributeBag
    {
        $selectedText = __('Selected');

        return new ComponentAttributeBag([
            'x-html' => <<<JS
                \$wire.\$parent.selectedOptions[record.id].length > 0
                && col === 'selected'
                && \$wire.\$parent.selectedOptions[record.id].length + ' $selectedText'
                || formatter(col, record)
            JS,
        ]);
    }
}
