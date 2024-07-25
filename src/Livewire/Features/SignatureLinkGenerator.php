<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Contracts\SignablePrintView;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SignatureLinkGenerator extends Component
{
    #[Modelable]
    public int $modelId;

    #[Locked]
    public string $modelType;

    public array $signedViews = [];

    public array $unsignedViews = [];

    public array $generatedUrls = [];

    public function mount(): void
    {
        $signable = array_keys(
            array_filter(
                $this->getModel()->resolvePrintViews(),
                fn ($printView) => is_a($printView, SignablePrintView::class, true)
            )
        );

        $this->signedViews = resolve_static(Media::class, 'query')
            ->where('model_id', $this->modelId)
            ->where('model_type', morph_alias($this->modelType))
            ->where('collection_name', 'signature')
            ->get()
            ->map(fn ($media) => Str::after($media->name, 'signature-'))
            ->toArray();
        $this->unsignedViews = array_diff($signable, $this->signedViews);
    }

    public function setPublicLink(string $printView): void
    {
        $this->generatedUrls[$printView] = URL::signedRoute(
            'signature.public',
            [
                'uuid' => $this->getModel()->uuid,
                'model' => morph_alias($this->modelType),
                'print-view' => $printView,
            ]
        );
    }

    public function render(): View
    {
        return view('flux::livewire.features.signature-link-generator');
    }

    protected function getModel(): OffersPrinting
    {
        return resolve_static($this->modelType, 'query')->whereKey($this->modelId)->firstOrFail();
    }
}
