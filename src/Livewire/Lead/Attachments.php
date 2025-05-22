<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\FolderTree;
use FluxErp\Models\Lead;
use Livewire\Attributes\Modelable;

class Attachments extends FolderTree
{
    #[Modelable]
    public ?int $modelId = null;

    public ?string $modelType = Lead::class;
}
