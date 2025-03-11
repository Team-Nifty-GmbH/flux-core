<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\DataTables\ProductOptionGroupList as BaseProductOptionGroupList;
use Illuminate\View\ComponentAttributeBag;

class ProductOptionGroupList extends BaseProductOptionGroupList
{
    public array $enabledCols = [
        'name',
        'selected',
    ];

    public bool $hasSidebar = false;

    public ?bool $isSearchable = true;

    public bool $showFilterInputs = false;

    public function save(): bool
    {
        if (! parent::save()) {
            return false;
        }

        $this->dispatch('data-table-row-clicked', record: $this->productOptionGroupForm);

        return true;
    }

    public function startSearch(): void
    {
        $this->filters = [[
            'name',
            'like',
            '%' . $this->search . '%',
        ]];

        parent::startSearch();
    }

    protected function allowSoftDeletes(): bool
    {
        return false;
    }

    protected function getCellAttributes(): ComponentAttributeBag
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

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-bind:class' => <<<'JS'
                record.id === productOptionGroup?.id && 'bg-indigo-100 dark:bg-indigo-800'
            JS,
        ]);
    }
}
