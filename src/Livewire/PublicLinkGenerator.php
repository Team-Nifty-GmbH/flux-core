<?php

namespace FluxErp\Livewire;


use FluxErp\Models\Media;
use FluxErp\Models\Order;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PublicLinkGenerator extends Component {


    public ?string $publicLink=null;


    public Order $order;

    public function mount(int $orderId): void
    {
        $this->order = app(Order::class)->find($orderId)->first();
    }

    public function setPublicLink()
    {
        $this->publicLink = URL::signedRoute('order.public', ['order' => $this->order->uuid]);
    }

    #[Computed]
    public function getSignature():bool
    {
        return is_null(app(Media::class)->query()
            ->where('model_type', 'order')
            ->where('model_id', $this->order->id)
            ->where('collection_name', 'signature')
            ->first());
    }

    public function render()
    {
            return view('flux::livewire.public-link-generator');
    }

}
