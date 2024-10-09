<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\FolderTree;
use FluxErp\Models\Contact;
use Livewire\Attributes\Modelable;

class Attachments extends FolderTree
{
    public ?string $modelType = Contact::class;

    #[Modelable]
    public ?int $modelId = null;
}
