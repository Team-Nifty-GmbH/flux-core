<?php

namespace FluxErp\Livewire\Portal\DataTables;

use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;

class OrderList extends DataTable
{
    protected string $model = Order::class;

    public array $enabledCols = [
        'order_number',
        'order_type.name',
        'commission',
        'payment_state',
    ];

    public array $sortable = ['*'];

    public array $availableRelations = [];

    public array $aggregatable = [];

    public bool $showFilterInputs = true;

    public function mount(): void
    {
        $this->enabledCols[] = auth()->user()->contact?->priceList?->is_net ? 'total_net_price' : 'total_gross_price';
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
            ]
        );

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('is_locked', true)
            ->with('orderType:id,name', 'currency:id,iso')
            ->where('contact_id', auth()->user()->contact_id);
    }

    public function getFormatters(): array
    {
        $formatters = parent::getFormatters();

        array_walk($formatters, function (&$formatter) {
            if ($formatter === 'money') {
                $formatter = ['money', ['property' => 'currency.iso']];
            }
        });

        return $formatters;
    }

    public function getScoutSearch(): \Laravel\Scout\Builder
    {
        return app($this->model)->search($this->search)->where('contact_id', auth()->user()->contact_id);
    }

    public function getReturnKeys(): array
    {
        return array_merge(parent::getReturnKeys(), ['currency.iso']);
    }
}
