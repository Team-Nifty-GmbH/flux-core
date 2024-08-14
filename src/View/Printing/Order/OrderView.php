<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\Order;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class OrderView extends PrintableView
{
    public Order $model;

    public array $summary = [];

    protected bool $showAlternatives = true;

    public function __construct(Order $order)
    {
        app()->setLocale($order->addressInvoice?->language?->language_code ?? config('app.locale'));

        $this->model = $order;
        $this->prepareModel();
    }

    public function getModel(): Order
    {
        return $this->model;
    }

    public function prepareModel(): void
    {
        $positions = to_flat_tree(
            $this->model
                ->orderPositions()
                ->whereNull('parent_id')
                ->when(! $this->showAlternatives, fn ($query) => $query->whereNot('is_alternative', true))
                ->with('tags')
                ->get()
                ->append('children')
                ->toArray()
        );

        $flattened = collect($positions)->map(
            function ($item) {
                return (object) $item;
            }
        );

        foreach ($flattened as $item) {
            if ($item->depth === 0 && $item->is_free_text && $item->has_children) {
                $this->summary[] = $item;
            }
        }

        $this->model->setRelation('orderPositions', $flattened);
    }

    public function render(): View|Factory
    {
        return view('print::order.order', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getFileName(): string
    {
        return $this->getSubject();
    }

    public function getSubject(): string
    {
        return __('Order').' '.$this->model->order_number;
    }
}
