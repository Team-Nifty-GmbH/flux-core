<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Contact;
use Livewire\Attributes\Modelable;

class Attachments extends FolderTree
{
    #[Modelable]
    public ?int $modelId = null;

    public ?string $modelType = Contact::class;
}
