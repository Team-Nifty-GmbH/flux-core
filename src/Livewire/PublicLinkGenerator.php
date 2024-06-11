<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class PublicLinkGenerator extends Component
{
    public ?string $publicLink = null;

    #[Modelable]
    public OrderForm $order;

    public function setPublicLink(): void
    {
        $this->publicLink = URL::signedRoute('order.public', ['order' => $this->order->uuid]);
    }

    #[Computed]
    public function getSignature(): bool
    {
        return is_null(
            app(Media::class)->query()
                ->where('model_type', 'order')
                ->where('model_id', $this->order->id)
                ->where('collection_name', 'signature')
                ->first()
        );
    }

    public function render()
    {
        return view('flux::livewire.public-link-generator');
    }
}
