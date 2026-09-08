<?php

namespace FluxErp\View\Components\Product;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class VariantBulkReset extends Component
{
    public array $fields = [];

    public string $title = '';

    public function __construct(array $counters = [])
    {
        foreach ($counters as $field => $stat) {
            $this->fields[] = [
                'name' => $field,
                'label' => __(Str::headline($field)),
                'summary' => __(':inheriting of :total variants inherit this field', $stat),
                'is_overridden' => $stat['inheriting'] < $stat['total'],
            ];
        }

        $this->title = __(':overridden of :total fields overridden on at least one variant', [
            'overridden' => count(array_filter($this->fields, fn (array $field): bool => $field['is_overridden'])),
            'total' => count($this->fields),
        ]);
    }

    public function render(): View
    {
        return view('flux::components.product.variant-bulk-reset');
    }
}
