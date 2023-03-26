<?php

namespace FluxErp\View\Components;

use FluxErp\Traits\HasFrontendAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class AlpinejsTable extends Component
{
    public array $enabledCols;

    public array $sortable;

    public array $filterable;

    public array $availableCols;

    public array $colLabels;

    public bool $showIndex;

    public array $indentedCols;

    public array $stretchCol;

    public array $formatters = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        array $enabledCols,
        string|Model $model = null,
        array $sortable = [],
        array $filterable = [],
        array $availableCols = [],
        bool $showIndex = false,
        array $indentedCols = [],
        array $stretchCol = [],
        array $formatters = []
    ) {
        $this->availableCols = $availableCols;

        $this->sortable = $sortable === ['*'] ? array_fill_keys($availableCols, true) : $sortable;
        $this->filterable = $filterable === ['*'] ? $availableCols : $filterable;

        $this->enabledCols = $enabledCols;

        $this->colLabels = array_flip($this->availableCols);

        array_walk($this->colLabels, function (&$value, $key) {
            $value = __($key);
        });

        $this->showIndex = $showIndex;

        $this->indentedCols = $indentedCols;

        $this->stretchCol = $stretchCol;

        $this->formatters = $formatters;
        if ($model && in_array(HasFrontendAttributes::class, class_uses_recursive($model))) {
            /** @var Model $model */
            $this->formatters = array_merge($model::typeScriptAttributes(), $this->formatters);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|string|\Closure
    {
        return function (array $data) {
            $data['selectable'] = $data['attributes']->has('wire:selectable');
            $data['model'] = $data['attributes']->wire('model')->value();

            $data['modelData'] = $data['model'] . (($this->page ?? false) ? '.data' : '');

            return view('components.alpinejs-table', $data);
        };
    }
}
