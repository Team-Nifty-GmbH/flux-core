<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\ProductOptionGroupList as BaseProductOptionGroupList;
use Illuminate\View\ComponentAttributeBag;

class ProductOptionGroupList extends BaseProductOptionGroupList
{
    public bool $showFilterInputs = false;

    public ?bool $isSearchable = true;

    public bool $hasSidebar = false;

    public array $enabledCols = [
        'name',
        'selected',
    ];

    public function startSearch(): void
    {
        $this->filters = [[
            'name',
            'like',
            '%'.$this->search.'%',
        ]];

        parent::startSearch();
    }

    public function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                record.id === productOptionGroup?.id && 'bg-primary-100 dark:bg-primary-800'
            JS,
        ]);
    }

    public function getCellAttributes(): ComponentAttributeBag
    {
        $selectedText = __('Selected');

        return new ComponentAttributeBag([
            'x-html' => <<<JS
                \$wire.\$parent.selectedOptions[record.id]?.length > 0
                && col === 'selected'
                && \$wire.\$parent.selectedOptions[record.id]?.length + ' $selectedText'
                || formatter(col, record)
            JS,
        ]);
    }

    public function save(): bool
    {
        if (! parent::save()) {
            return false;
        }

        $this->dispatch('data-table-row-clicked', record: $this->productOptionGroupForm);

        return true;
    }
}
