<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class PublicLinkGenerator extends Component
{
    #[Modelable]
    public OrderForm $order;

    public array $signedUrls = [];

    public array $unsignedDocuments = [];

    public array $generatedUrls = [];

    public function mount(): void
    {
        $this->signedUrls = array_map(function (array $item) {
            return $item['custom_properties']['order_type'];
        }, app(Media::class)->query()
            ->where('model_id', $this->order->id)
            ->where('collection_name', 'signature')->get()->toArray());

        $mergedArray = array_merge($this->order->order_type['print_layouts'], $this->signedUrls);

        $valueCounts = array_count_values($mergedArray);

        $this->unsignedDocuments = array_values(array_filter($mergedArray, function ($value) use ($valueCounts) {
            return $valueCounts[$value] === 1;
        }));

    }

    public function setPublicLink(string $printView): void
    {
        $key = array_search($printView, $this->unsignedDocuments);
        if ($key !== false) {
            unset($this->unsignedDocuments[$key]);
        }
        $this->unsignedDocuments = array_values($this->unsignedDocuments);

        $this->generatedUrls[$printView] = URL::signedRoute(
            'order.public',
            [
                'order' => $this->order->uuid,
                'print-view' => $printView
            ]
        );
    }

    public function render(): View
    {
        return view('flux::livewire.public-link-generator');
    }
}
