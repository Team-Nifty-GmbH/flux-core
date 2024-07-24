<?php

namespace FluxErp\Livewire;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class PublicLinkGenerator extends Component
{
    #[Modelable]
    public int $modelId;

    #[Locked]
    public string $modelType;

    public array $signedUrls = [];

    public array $unsignedDocuments = [];

    public array $generatedUrls = [];

    public function mount(): void
    {
        $this->signedUrls = array_map(
            function (array $item) {
                return $item['custom_properties']['order_type'];
            },
            app(Media::class)->query()
                ->where('model_id', $this->modelId)
                ->where('model_type', morph_alias($this->modelType))
                ->where('collection_name', 'signature')
                ->get()
                ->toArray()
        );

        $mergedArray = array_merge(
            array_keys($this->getModel()->resolvePrintViews()),
            $this->signedUrls
        );

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
            'signature.public',
            [
                'uuid' => $this->getModel()->uuid,
                'model' => morph_alias($this->modelType),
                'print-view' => $printView
            ]
        );
    }

    public function render(): View
    {
        return view('flux::livewire.public-link-generator');
    }

    protected function getModel(): OffersPrinting
    {
        return resolve_static($this->modelType, 'query')->whereKey($this->modelId)->firstOrFail();
    }
}
