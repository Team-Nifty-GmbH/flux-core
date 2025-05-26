<?php

namespace FluxErp\Livewire\Support;

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Contracts\SignablePrintView;
use FluxErp\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\Modelable;
use Livewire\Component;

abstract class SignatureLinkGenerator extends Component
{
    public array $generatedUrls = [];

    #[Modelable]
    public int $modelId;

    public array $signedViews = [];

    public array $unsignedViews = [];

    protected string $modelType;

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

    public function render(): View
    {
        return view('flux::livewire.support.signature-link-generator');
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

    protected function getModel(): OffersPrinting
    {
        return resolve_static($this->modelType, 'query')->whereKey($this->modelId)->firstOrFail();
    }
}
