<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class PublicLinkGenerator extends Component
{
    #[Modelable]
    public OrderForm $order;

    public array $signed_urls = [];

    public array $unsigned_documents = [];

    public array $generated_urls = [];

    public function mount(): void
    {
        $this->signed_urls = array_map(function (array $item) {
            return $item['custom_properties']['order_type'];
        }, app(Media::class)->query()
            ->where('model_id', $this->order->id)
            ->where('collection_name', 'signature')->get()->toArray());

        $mergedArray = array_merge($this->order->order_type['print_layouts'], $this->signed_urls);

        $valueCounts = array_count_values($mergedArray);

        $this->unsigned_documents = array_values(array_filter($mergedArray, function ($value) use ($valueCounts) {
            return $valueCounts[$value] === 1;
        }));

    }

    public function setPublicLink(string $orderType): void
    {

        $key = array_search($orderType, $this->unsigned_documents);
        if ($key !== false) {
            unset($this->unsigned_documents[$key]);
        }
        $this->unsigned_documents = array_values($this->unsigned_documents);

        $this->generated_urls[$orderType] = URL::signedRoute('order.public', ['order' => $this->order->uuid]) . "&orderType={$orderType}";

    }

    public function render()
    {
        return view('flux::livewire.public-link-generator');
    }
}
