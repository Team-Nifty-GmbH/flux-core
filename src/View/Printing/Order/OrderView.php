<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

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

    public function getModel(): Order
    {
        return $this->model;
    }

    public function getSubject(): string
    {
        return __('Order') . ' ' . $this->model->order_number;
    }

    public function prepareModel(): void
    {
        resolve_static(OrderPosition::class, 'addGlobalScope', [
            'scope' => 'sorted',
            'implementation' => function (Builder $query): void {
                $query->ordered()
                    ->with(['tags', 'product.unit:id,name,abbreviation'])
                    ->when(! $this->showAlternatives, fn (Builder $query) => $query->whereNot('is_alternative', true));
            },
        ]);

        $positions = array_map(
            fn (array $item) => app(OrderPosition::class)->setRawAttributes($item),
            to_flat_tree(
                resolve_static(OrderPosition::class, 'familyTree')
                    ->where('order_id', $this->model->getKey())
                    ->whereNull('parent_id')
                    ->get()
                    ->toArray()
            )
        );

        $flattened = app(OrderPosition::class)->newCollection($positions);

        foreach ($flattened as $item) {
            if ($item->depth === 0 && $item->is_free_text && $item->has_children) {
                $this->summary[] = $item;
            }
        }

        $this->model->setRelation('orderPositions', $flattened);
    }
}
