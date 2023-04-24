<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderView extends Component
{
    public Order $model;

    public array $summary = [];

    public string $title = '';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        // Set locale to addressInvoice language if it is set
        app()->setLocale($order->addressInvoice?->language?->language_code ?? config('app.locale'));

        $this->model = $order;

        $this->prepareModel();
    }

    public function prepareModel(): void
    {
        $positions = to_flat_tree(
            $this->model
                ->orderPositions()
                ->whereNull('parent_id')
                ->whereNot('is_alternative', true)
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
            'title' => $this->title,
        ]);
    }
}
