<?php

namespace FluxErp\View\Printing\Order;

use FluxErp\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Retoure extends OrderView
{
    public function __construct(Order $order)
    {
        parent::__construct($order);

        $this->model->load(['parent', 'orderType']);
    }

    public function render(): View|Factory
    {
        return view('print::order.retoure', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getFileName(): string
    {
        return __('Retoure') . ' ' . $this->model->order_number;
    }
}
