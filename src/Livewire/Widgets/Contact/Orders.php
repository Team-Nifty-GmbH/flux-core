<?php

namespace FluxErp\Livewire\Widgets\Contact;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;

class Orders extends ValueBox
{
    use IsTimeFrameAwareWidget;

    public ?int $contactId = null;

    public static function dashboardComponent(): array|string
    {
        return Statistics::class;
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $orders = resolve_static(Order::class, 'query')
            ->where('contact_id', $this->contactId)
            ->whereRelation('orderType', 'order_type_enum', OrderTypeEnum::Order)
            ->whereBetween('order_date', [$this->getStart(), $this->getEnd()]);

        $this->sum = $orders->clone()->count();
        $this->subValue = e(
            Number::format($orders->clone()->sum('total_net_price'), 2)
            . ' ' . resolve_static(Currency::class, 'default')?->symbol
        );
    }

    protected function icon(): string
    {
        return 'shopping-cart';
    }
}
