<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\FolderTree;
use FluxErp\Models\Address;
use Livewire\Attributes\Modelable;

class Attachments extends FolderTree
{
    public ?string $modelType = Address::class;

    #[Modelable]
    public ?int $modelId = null;
}
