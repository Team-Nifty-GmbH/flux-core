<?php

namespace FluxErp\Livewire\Contact;

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Contact;

class Attachments extends FolderTree
{
    protected string $modelType = Contact::class;
}
