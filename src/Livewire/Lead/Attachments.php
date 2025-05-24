<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\FolderTree;
use FluxErp\Models\Lead;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class Attachments extends FolderTree
{
    #[Modelable]
    public ?int $modelId = null;

    #[Locked]
    public ?string $modelType = Lead::class;
}
