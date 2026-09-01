<?php

namespace FluxErp\Tests\Fixtures\Livewire;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class TabsFixtureChild extends Component
{
    #[Modelable]
    public ?int $modelId = null;

    public ?int $loadedModelId = null;

    public function render(): string
    {
        // mimics load-once children like Support\Activities that only load while empty
        if (is_null($this->loadedModelId) && $this->modelId) {
            $this->loadedModelId = $this->modelId;
        }

        return '<div>Tab child loaded model: {{ $loadedModelId ?? "none" }}</div>';
    }
}
