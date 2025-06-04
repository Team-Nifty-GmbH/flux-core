<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Address;
use Livewire\Attributes\Modelable;

class Attachments extends FolderTree
{
    #[Modelable]
    public ?int $modelId = null;

    public ?string $modelType = Address::class;
}
