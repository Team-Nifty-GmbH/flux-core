<?php

namespace FluxErp\Livewire\Portal\DataTables;

use FluxErp\Livewire\DataTables\BaseDataTable;
use FluxErp\Models\Order;
use Laravel\Scout\Builder;

class OrderList extends BaseDataTable
{
    public array $aggregatable = [];

    public array $availableRelations = [];

    public array $enabledCols = [
        'order_number',
        'order_type.name',
        'commission',
        'payment_state',
    ];

    public bool $showFilterInputs = true;

    public array $sortable = ['*'];

    protected string $model = Order::class;

    public function mount(): void
    {
        $this->enabledCols[] = auth()->user()->priceList?->is_net ? 'total_net_price' : 'total_gross_price';
        $this->availableCols = array_merge(
            $this->enabledCols,
            [
                'id',
                'order_date',
                'total_net_price',
                'total_gross_price',
                'invoice_number',
                'invoice_date',
                'delivery_state',
                'state',
                'currency.iso',
                'header',
                'footer',
            ]
        );

        parent::mount();
    }

    public function getFormatters(): array
    {
        $formatters = parent::getFormatters();

        array_walk($formatters, function (&$formatter): void {
            if ($formatter === 'money') {
                $formatter = ['money', ['property' => 'currency.iso']];
            }
        });

        return $formatters;
    }

    public function getScoutSearch(): Builder
    {
        return app($this->model)->search($this->search)->where('contact_id', auth()->user()->contact_id);
    }

    protected function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['currency.iso']);
    }
}
